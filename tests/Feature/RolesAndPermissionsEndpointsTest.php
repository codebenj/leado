<?php

namespace Tests\Feature;

use App\User;
use App\LeadEscalation;
use App\Organisation;
use App\Customer;
use App\Lead;
use App\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use CountriesDataSeeder;
use SettingSeeder;
use RoleSeeder;

class RolesAndPermissionsEndpointsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var \App\User */
    protected $user;

    /** @var \App\Organisation */
    protected $organisation;

    /** @var \App\OrganizationUser */
    protected $organization_user;

    /** @var \App\LeadEscalation */
    protected $leadEscalation;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();

        $this->seed(SettingSeeder::class);
        $this->seed(CountriesDataSeeder::class);
        $this->seed(RoleSeeder::class);

        # Admin
        $this->admin = factory(User::class)->create();
        $this->admin->assignRole('administrator');

        # Org 1
        $this->user = factory(User::class)->create();
        $this->user->assignRole('organisation');
        $this->organisation = factory(Organisation::class)->create([
            'user_id' => $this->user->id
        ]);

        $organisation_user = factory(OrganizationUser::class)->create(
            ['user_id' => $this->user->id, 'organisation_id' => $this->organisation->id]
        );

        $this->customer = factory(Customer::class)->create();

        $this->lead = factory(Lead::class)->create([
            'customer_id' => $this->customer->id
        ]);

        $this->leadEscalation = factory(LeadEscalation::class)->make([
            'lead_id' => $this->lead->id,
            'organisation_id' => $this->organisation->id
        ]);
    }

    protected function setupPermissions()
    {
        Role::findOrCreate('administrator');
        Role::findOrCreate('organisation');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function test_get_roles_endpoint_success() {
        $this->actingAs($this->admin)
            ->getJson('/api/v1/roles')
            ->assertSuccessful()
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function test_get_permissions_endpoint_success() {
        $this->actingAs($this->admin)
            ->getJson('/api/v1/permissions')
            ->assertSuccessful()
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function test_get_roles_endpoint_not_allowed_success() {
        $this->actingAs($this->user)
            ->getJson('/api/v1/roles')
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function test_get_permissions_endpoint_not_allowed_success() {
        $this->actingAs($this->user)
            ->getJson('/api/v1/permissions')
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function test_admin_create_role_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/roles', [
                //"name" => "admin1",
                "name" => $this->faker->word //use any string just unique
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();
    }


    /** @test */
    public function test_admin_create_permission_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/permissions', [
                "name" => "can edit org"
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();
    }

    /** @test */
    public function test_admin_update_role_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/roles', [
                "name" => "admin2",
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();

        $response = $this->actingAs($this->admin)
            ->putJson('/api/v1/roles/' . $response['data']['id'] . '/', [
                "name" => "admin3",
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();

    }


    /** @test */
    public function test_admin_update_permission_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/permissions', [
                "name" => "can delete org"
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();

        $response = $this->actingAs($this->admin)
            ->putJson('/api/v1/permissions/' . $response['data']['id'] . '/', [
                "name" => "can display org",
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();
    }
}
