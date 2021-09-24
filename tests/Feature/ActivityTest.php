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

class ActivityTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    //use WithFaker;

    /** @var \App\User */
    protected $user;

    /** @var \App\Organisation */
    protected $organisation;

    /** @var \App\OrganizationUser */
    protected $organization_user;

    /** @var \App\LeadEscalation */
    protected $leadEscalation;

    protected $lead;

    protected $customer;

    public function tearDown(): void{
        parent::tearDown();
    }

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
            'user_id' => $this->user->id,
        ]);

        $organisation_user = factory(OrganizationUser::class)->create(
            ['user_id' => $this->user->id, 'organisation_id' => $this->organisation->id]
        );

        $this->customer = factory(Customer::class)->create();

        $this->lead = factory(Lead::class)->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->leadEscalation = factory(LeadEscalation::class)->make([
            'lead_id' => $this->lead->id,
            'organisation_id' => $this->organisation->id,
        ]);
    }

    protected function setupPermissions()
    {
        Role::findOrCreate('administrator');
        Role::findOrCreate('organisation');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();

    }

    /** @test */
    public function test_admin_create_lead_endpoint_success() {
        $enquirer = factory(Lead::class)->make([
            'escalation_level' => 'Accept Or Decline',
            'escalation_status' => 'Pending',
            'additional_information' => $this->faker->sentence,
            'email' => $this->faker->unique()->safeEmail,
            'contact_number' => $this->faker->e164PhoneNumber,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postcode' => "6000",
            'country' => 'Australia',
            'organisation_id' => $this->organisation->id,
        ])->toArray();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/leads', $enquirer)
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();

        $this->assertArrayHasKey('lead_escalation', $response['data']);
        $this->assertArrayHasKey('escalation_level', $response['data']['lead_escalation']);
        $this->assertArrayHasKey('escalation_status', $response['data']['lead_escalation']);
        $this->assertTrue($response['data']['lead_escalation']['escalation_level'] == 'Accept Or Decline');
        $this->assertTrue($response['data']['lead_escalation']['escalation_status'] == 'Pending');
    }

    /** @test */
    public function test_admin_get_activities_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/activities')
            ->assertSuccessful()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'last_page'])
            ->json();

        $this->assertTrue(count($response['data']) > 0);
    }

    /** @test */
    public function test_admin_get_activity_message_success() {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/activities')
            ->assertSuccessful()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'last_page'])
            ->json();
    }
}
