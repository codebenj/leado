<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use RoleSeeder;

class SettingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $administrator_user;
    protected $organisation_user;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupPermission();

        $this->seed(RoleSeeder::class);

        $this->administrator_user = factory(User::class)->create(['role_id' => 2]);
        $this->administrator_user->assignRole('administrator');

        $this->organisation_user = factory(User::class)->create();
        $this->organisation_user->assignRole('organisation');
    }

    protected function setupPermission(){
        app()['cache']->forget('spatie.permission.cache');

        Role::findOrCreate('administrator');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function authorized_insert_success()
    {
        //change the keys if is error since the key is unique
        $setting_data = array(
            'key' => 'ip-ars',
            'name' => 'In Progress - Awaiting Response',
            'value' => 24,
            'metadata' => array(
                'type' => 'days',
                'level' => 'In Progress',
                'status' => 'Awaiting Response',
                'description' => 'If the Status is not updated within this time a reminder email will be sent.'
            )
        );

        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/admin/setting', $setting_data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_insert_success()
    {
        //change the keys ifs has error since the key is unique
        $setting_data = array(
            'key' => 'ip-ar',
            'name' => 'In Progress - Awaiting Response',
            'value' => 24,
            'metadata' => array(
                'type' => 'days',
                'level' => 'In Progress',
                'status' => 'Awaiting Response',
                'description' => 'If the Status is not updated within this time a reminder email will be sent.'
            )
        );

        //user is not administrator
        $response = $this->actingAs($this->organisation_user)
            ->postJson('/api/v1/admin/setting', $setting_data);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_index_success(){
        $response = $this->actingAs($this->administrator_user)
            ->json('GET', '/api/v1/admin/setting', ['key' => 'ip-arxxx']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_index_success(){
        $response = $this->actingAs($this->organisation_user)
            ->json('GET', '/api/v1/admin/setting', ['key' => 'ip-ar']);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_show_successs(){
        $response = $this->actingAs($this->administrator_user)
            ->json('GET', '/api/v1/admin/setting/1');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_show_successs(){
        $response = $this->actingAs($this->organisation_user)
            ->json('GET', '/api/v1/admin/setting/1');

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_update_success(){
        //change the keys if is has error since the key is unique
        $setting_data = array(
            'key' => 'ip-ar',
            'name' => 'In Progress - Awaiting Response',
            'value' => 24,
            'metadata' => array(
                'type' => 'days',
                'level' => 'In Progress',
                'status' => 'Awaiting Response',
                'description' => 'If the Status is not updated within this time a reminder email will be sent.'
            )
        );

        $response = $this->actingAs($this->administrator_user)
            ->patchJson('/api/v1/admin/setting/1', $setting_data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_update_success(){
        //change the keys if is has error since the key is unique
        $setting_data = array(
            'key' => 'ip-ar',
            'name' => 'In Progress - Awaiting Response',
            'value' => 24,
            'metadata' => array(
                'type' => 'days',
                'level' => 'In Progress',
                'status' => 'Awaiting Response',
                'description' => 'If the Status is not updated within this time a reminder email will be sent.'
            )
        );

        $response = $this->actingAs($this->organisation_user)
            ->patchJson('/api/v1/admin/setting/1', $setting_data);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function authorized_destroy_success(){
        $response = $this->actingAs($this->administrator_user)
            ->deleteJson('/api/v1/admin/setting/1');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function unauthorized_destroy_success(){
        $response = $this->actingAs($this->organisation_user)
            ->deleteJson('/api/v1/admin/setting/2');

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function add_update_admin_email_receivers(){
        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/admin/setting/admin_email_receivers', [
                'receivers' => 'foo@examle.com,foobar@gmail.com',
                'enquirer_message' => 'foobar1010',
                'day_of_week' => 1,
                'time' => '08:00',
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function invalid_email_add_update_admin_email_receivers(){
        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/admin/setting/admin_email_receivers', [
                'receivers' => 'foo@examle.com,foobar',
                'enquirer_message' => 'foobar1010'
            ]);

        $response->assertStatus(422);
        //$response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function unauthorized_add_update_admin_email_receivers(){
        $response = $this->actingAs($this->organisation_user)
            ->postJson('/api/v1/admin/setting/admin_email_receivers', [
                'emails' => 'foo@examle.com,foobar@gmail.com',
                'enquirer_message' => 'foobar1010'
            ]);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    // public function authorized_upload_logo_success(){
    //     $name = 'any-name.jpg';
    //     //physical path
    //     $path = 'C:\Users\norma\Downloads\sample.jpg';
    //     $file = new UploadedFile($path, $name);

    //     //on post need file name "image"
    //     $response = $this->actingAs($this->administrator_user)
    //         ->postJson('/api/v1/admin/setting/upload_logo', ['image' => $file]);

    //     $response->assertSuccessful();
    //     $response->assertJsonStructure(['success', 'message', 'data']);
    //     $response->json();
    // }
}
