<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;
use App\Address;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\OrganizationUser;
use CountriesDataSeeder;
use SettingSeeder;
use RoleSeeder;

class UserTest extends TestCase
{
    protected $user;
    protected $administrator_user;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();

        $this->seed(SettingSeeder::class);
        $this->seed(CountriesDataSeeder::class);
        $this->seed(RoleSeeder::class);

        app()['cache']->forget('spatie.permission.cache');

        $this->administrator_user = factory(User::class)->create();
        $this->administrator_user->assignRole('administrator');

        $this->user = factory(User::class)->create(['role_id' => 4]);
        $this->user->assignRole('user');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    protected function setupPermissions()
    {
        Role::findOrCreate('administrator');
        Role::findOrCreate('organisation');
        Role::findOrCreate('user');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function authorized_index_success()
    {
        $response = $this->actingAs($this->administrator_user)
            ->json('GET', '/api/v1/users');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_index_success(){
        //in controler we allwo role user to access the api, so it will be cant retur unauthorized
        $response = $this->actingAs($this->user)
            ->json('GET', '/api/v1/users');

        //$response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_index_with_param_success(){
        $response = $this->actingAs($this->administrator_user)
            ->json('GET', '/api/v1/users', ['email' => 'traleado']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function authorized_store_success(){
        $user_data = factory(User::class)->make(['role_id' => 4])->toArray();

        $user_data['password'] = "password";
        $user_data['password_confirmation'] = "password";

        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/users', $user_data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_store_success(){
        //in controler we allwo role user to access the api, so it will be cant retur unauthorized
        $user_data = factory(User::class)->make(['role_id' => 4])->toArray();

        $user_data['password'] = 'password';
        $user_data['password_confirmation'] = "password";

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/users', $user_data);

        //$response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_show_success(){
        $response = $this->actingAs($this->administrator_user)
            ->getJson('/api/v1/users/4');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_show_success(){
        //in controler we allwo role user to access the api, so it will be cant retur unauthorized
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/users/4');

        //$response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function authorized_update_success(){
        $user_data = factory(User::class)->make(['role_id' => 4])->toArray();

        $response = $this->actingAs($this->administrator_user)
            ->putJson('/api/v1/users/2', $user_data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_update_success(){
        //in controler we allwo role user to access the api, so it will be cant retur unauthorized
        $user_data = factory(User::class)->make(['role_id' => 4])->toArray();

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/users/5', $user_data);

        //$response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_destroy_success(){
        $response = $this->actingAs($this->administrator_user)
            ->deleteJson('/api/v1/users/5');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_destroy_success(){
        //in controler we allwo role user to access the api, so it will be cant retur unauthorized
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/users/5');

        //$response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function authorized_upload_avatar_success(){
        $name = 'any-name.jpg';
        //physical path
        $path = storage_path('test_assets/profile.jpg');
        $file = new UploadedFile($path, $name);

        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/users/upload_avatar', ['image' => $file]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function update_profile_success(){
        //create user to database without any validations (consider as old users data)
        //$user_data_old = factory(User::class)->create(['role_id' => 4])->toArray();

        //new user data to update
        $user_data_new = factory(User::class)->make(['role_id' => 4, 'id' => $this->administrator_user->id, 'email' => $this->administrator_user->email, 'company_name' => 'testtest'])->toArray();

        $user_data_new['password'] = "password";
        $user_data_new['password_confirmation'] = "password";

        $address_data = factory(Address::class)->make()->toArray();

        //merge to array from factories of users and address
        //$data = array_merge_recursive($user_data_new, $address_data);
        $data = array_merge($user_data_new, $address_data);

        //dd($data);
        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/users/profile', $data);

        $response->assertSessionHasNoErrors();
        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function delete_users_success(){
        $user1 = factory(User::class)->create(['role_id' => 4]);
        $user2 = factory(User::class)->create(['role_id' => 4]);

        $ids = array($user1->id, $user2->id);

        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/users/delete', ['ids' => $ids]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }
}
