<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\UploadedFile;
use App\User;
use App\OrgLocator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use RoleSeeder;

class OrgLocatorTest extends TestCase
{
    protected $user;

    protected $admin_user;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupPermission();

        $this->seed(RoleSeeder::class);

        $this->user = factory(User::class)->create();
        $this->user->assignRole('organisation');

        //$this->admin_user = factory(User::class)->create(['role_id' => 2]);
        $this->admin_user = factory(User::class)->create();
        $this->admin_user->assignRole('administrator');
    }

    protected function setupPermission(){
        Role::findOrCreate('organisation');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function test_index_not_administrator_with_parameters_success(){
        $response = $this->actingAs($this->user)
            ->json('GET', '/api/v1/org-locator', ['org_id' => '6512423']);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function test_index_administrator_with_parameters_success(){
        $response = $this->actingAs($this->admin_user)
            ->json('GET', '/api/v1/org-locator', ['org_id' => '6512423']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function test_index_not_login_user_with_parameters_success(){
        $response = $this->json('GET', '/api/v1/org-locator', ['org_id' => '6512423']);

        $response->assertUnauthorized();
        $response->json();
    }

    /** @test */
    public function test_store_administrator_success()
    {
        $org_locator = factory(OrgLocator::class)->make()->toArray();

        $response = $this->actingAs($this->admin_user)
            ->postJson('/api/v1/org-locator', $org_locator);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function test_store_administrator_failed()
    {
        $org_locator = factory(OrgLocator::class)->make()->toArray();

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org-locator', $org_locator);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function test_update_administrator_success(){
        $org_locator = factory(OrgLocator::class)->make()->toArray();

        $response = $this->actingAs($this->admin_user)
            ->patchJson('/api/v1/org-locator/1', $org_locator);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function test_update_administrator_failed(){
        $org_locator = factory(OrgLocator::class)->make()->toArray();

        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/org-locator/1', $org_locator);

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function show_administrator_success(){
        $response = $this->actingAs($this->admin_user)
            ->getJson('/api/v1/org-locator/1');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function show_administrator_failed(){
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org-locator/1');

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function destroy_administrator_success(){
        $response = $this->actingAs($this->admin_user)
            ->deleteJson('/api/v1/org-locator/1');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function destroy_administrator_failed(){
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/org-locator/1');

        $response->assertUnauthorized();
        $response->assertJsonStructure(['success', 'message']);
        $response->json();
    }

    /** @test */
    public function import_administrator_success(){

        $name = 'stores.xlsx';
        //physical path
        $path = storage_path('test_assets/stores.xlsx');
        $file = new UploadedFile($path, $name);
        //on post need file name "import_file"
        $response = $this->actingAs($this->admin_user)
            ->postJson('/api/v1/org-locator/import', ['import_file' => $file]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function mass_delete_administrator_success(){
        $response = $this->actingAs($this->admin_user)
            ->postJson('/api/v1/org-locator/delete', ['ids' => [1,2,3]]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function logs_administrator_success(){
        $response = $this->actingAs($this->admin_user)
            ->getJson('/api/v1/org-locator/logs');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }
}
