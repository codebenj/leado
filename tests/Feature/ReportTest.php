<?php

namespace Tests\Feature;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\LeadEscalation;
use App\Organisation;
use App\Customer;
use App\Lead;
use App\OrganizationUser;
use CountriesDataSeeder;
use OrganizationSeeder;
use LeadEscalationSeeder;
use SettingSeeder;
use RoleSeeder;

class ReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /** @var \App\User */
    protected $administrator;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();

        $this->seed(CountriesDataSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(RoleSeeder::class);

        # Admin
        $this->administrator = factory(User::class)->create();
        $this->administrator->assignRole('administrator');

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

        # Org 2
        $this->user_2 = factory(User::class)->create();
        $this->user_2->assignRole('organisation');
        $this->organisation_2 = factory(Organisation::class)->create([
            'user_id' => $this->user_2->id
        ]);

        $organisation_user_2 = factory(OrganizationUser::class)->create(
            ['user_id' => $this->user_2->id, 'organisation_id' => $this->organisation_2->id]
        );
    }

    protected function setupPermissions()
    {
        $this->app->make(PermissionRegistrar::class)->registerPermissions();

        Role::findOrCreate('administrator');
    }

    /** @test */
    public function medium_breakdown_success()
    {
        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/medium-breakdown');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function medium_breakdown_with_params_success(){
        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/medium-breakdown', ['state' => 'Alabama']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function organisation_breakdown_success(){
        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/organisation-breakdown');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function organisation_breakdown_with_params_success(){
        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/organisation-breakdown', ['state' => 'xxxxxx']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function leads_won_breakdown_with_params_success(){
        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/leads-won-breakdown');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function lead_stats_success(){
        $this->customer = factory(Customer::class)->create();

        $this->lead = factory(Lead::class)->create([
            'customer_id' => $this->customer->id
        ]);

        $this->leadEscalation = factory(LeadEscalation::class)->create([
            'lead_id' => $this->lead->id,
            'organisation_id' => $this->organisation->id
        ]);

        $lead = $this->leadEscalation;

        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/lead/'.$lead->id.'/stat');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function organization_stats_success(){
        $org = Organisation::inRandomOrder()->first();

        $response = $this->actingAs($this->administrator)
            ->json('GET', '/api/v1/admin/reports/organization/'.$org->id.'/stat');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function export_advertising_medium_breakdown_success(){
        $response = $this->actingAs($this->administrator)
            ->json('POST', '/api/v1/admin/reports/export/advertising-medium-breakdown');

        $response->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');
    }
}
