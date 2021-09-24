<?php

namespace Tests\Feature;

use App\Lead;
use App\LeadJob;
use App\Organisation;
use App\OrganizationUser;
use App\Customer;
use App\LeadEscalation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\User;
use Tests\TestCase;
use CountriesDataSeeder;
use SettingSeeder;
use RoleSeeder;

class LeadJobTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $organisation_user;

    protected $organisation;

    public function setUp(): void
    {
        parent::setUp();

        app()['cache']->forget('spatie.permission.cache');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();

        $this->seed(SettingSeeder::class);
        $this->seed(CountriesDataSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->organisation_user = factory(User::class)->create();

        $this->organisation_user->assignRole('organisation');

        $this->organisation = factory(Organisation::class)->create([
            'user_id' => $this->organisation_user->id
        ]);

        factory(OrganizationUser::class)->create(
            ['user_id' => $this->organisation_user->id, 'organisation_id' => $this->organisation->id]
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

    /** @test */
    public function store_success(){
        $data = factory(LeadJob::class)->make()->toArray();

        $response = $this->actingAs($this->organisation_user)
            ->postJson('/api/v1/lead/jobs', $data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function show_success(){
        $response = $this->actingAs($this->organisation_user)
            ->getJson('/api/v1/lead/jobs/1');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function update_success(){
        $data = factory(LeadJob::class)->make()->toArray();

        $response = $this->actingAs($this->organisation_user)
            ->putJson('/api/v1/lead/jobs/1', $data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function destroy_success(){
        $data = factory(LeadJob::class)->create()->toArray();

        $response = $this->actingAs($this->organisation_user)
            ->deleteJson('/api/v1/lead/jobs/'.$data['id']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function index_success(){
        $response = $this->actingAs($this->organisation_user)
            ->getJson('/api/v1/lead/jobs/');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }
}
