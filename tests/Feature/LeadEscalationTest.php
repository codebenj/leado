<?php

namespace Tests\Feature;

use App\Customer;
use App\User;
use App\LeadEscalation;
use App\Organisation;
use App\OrganizationUser;
use App\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use RoleSeeder;
use SettingSeeder;
use CountriesDataSeeder;

class LeadEscalationTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    //use WithFaker;

    /** @var \App\User */
    protected $admin;

    /** @var \App\User */
    protected $user, $user_2;

    /** @var \App\Organisation */
    protected $organisation, $organisation_2;

    /** @var \App\OrganizationUser */
    protected $organization_user;

    /** @var \App\LeadEscalation */
    protected $leadEscalation;

    protected $lead;

    protected $customer;

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

        # Org 2
        $this->user_2 = factory(User::class)->create();
        $this->user_2->assignRole('organisation');
        $this->organisation_2 = factory(Organisation::class)->create([
            'user_id' => $this->user_2->id,
        ]);

        $organisation_user_2 = factory(OrganizationUser::class)->create(
            ['user_id' => $this->user_2->id, 'organisation_id' => $this->organisation_2->id]
        );
    }

    protected function setupPermissions()
    {
        Role::findOrCreate('administrator');
        Role::findOrCreate('organisation');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function test_get_leads_enpoint_success() {
        $this->actingAs($this->user)
            ->getJson('/api/v1/leads')
            ->assertSuccessful()
            ->assertJsonStructure(['data', 'success']);
    }

    /** @test */
    public function test_retrieve_lead_endpoint_success() {

        $this->customer = factory(Customer::class)->create();

        $this->lead = factory(Lead::class)->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->leadEscalation = factory(LeadEscalation::class)->create([
            'lead_id' => $this->lead->id,
            'organisation_id' => $this->organisation->id,
        ]);

        $response = $this->actingAs($this->user);

        $response->getJson('/api/v1/leads/' . $this->leadEscalation->id)
            ->assertSuccessful()
            ->assertJsonStructure(['success'])
            ->json();

        // It should be obj success is true
        //$this->assertTrue($response['success']); already check above, assertJsonStructure
    }

    /** @test */
    public function test_retrieve_lead_endpoint_failed() {
        // It should fail, user_2 doesn't have permission to view this lead
        $response = $this->actingAs($this->user_2)
            ->getJson('/api/v1/leads/' . $this->leadEscalation->lead_id . '/')
            ->assertStatus(400)
            ->assertJsonStructure(['success', 'message'])
            ->json();
    }

    /** @test */
    public function test_admin_create_lead_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/leads', [
                "escalation_level" => "Accept Or Decline",
                "escalation_status" => "Pending",
                "color" => "blue",
                "customer_type" => "SUPPLY & INSTALL",
                "house_type" => "2 storey house",
                "roof_preference" => "Tile",
                "commercial" => "test",
                "situation" => "test",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
                "comments" => "Test",
                "source" => "Newspaper",
                "staff_comments" => "Test",
                "sale_string" => "$0.00",
                "sale" => 0.00,
                "organisation_id" => $this->organisation->id,
                "email" => (substr($this->faker->uuid, -12, 12) . '_' . $this->faker->unique()->safeEmail),
                "contact_number" => $this->faker->e164PhoneNumber,
                "first_name" => $this->faker->firstName,
                "last_name" => $this->faker->lastName,
                "address" => $this->faker->streetAddress,
                "city" => $this->faker->city,
                "state" => $this->faker->state,
                "postcode" => "6000",
                "country" => "Australia",
                "use_for" => "Single Storey dwelling",
            ]);
            $response->assertSuccessful();
            $response->assertJsonStructure(['success', 'data']);
            $response->json();

        $this->assertArrayHasKey('lead_escalation', $response['data']);
        $this->assertArrayHasKey('escalation_level', $response['data']['lead_escalation']);
        $this->assertArrayHasKey('escalation_status', $response['data']['lead_escalation']);
        $this->assertTrue($response['data']['lead_escalation']['escalation_level'] == 'Accept Or Decline');
        $this->assertTrue($response['data']['lead_escalation']['escalation_status'] == 'Pending');
    }

    /** @test */
    public function test_org_create_lead_endpoint_failed() {
        $response = $this->actingAs($this->user_2)
            ->postJson('/api/v1/leads', [
                "escalation_level" => "Accept or Decline",
                "escalation_status" => "Pending",
                "color" => "blue",
                "customer_type" => "I'm a homeowner",
                "house_type" => "2 storey house",
                "roof_preference" => "Tile",
                "commercial" => "test",
                "situation" => "test",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
                "comments" => "Test",
                "source" => "Newspaper",
                "staff_comments" => "Test",
                "sale_string" => "$0.00",
                "sale" => 0.00,
                "organisation_id" => $this->organisation->id,
                "email" => (substr($this->faker->uuid, -12, 12) . '_' . $this->faker->unique()->safeEmail),
                "contact_number" => $this->faker->e164PhoneNumber,
                "first_name" => $this->faker->firstName,
                "last_name" => $this->faker->lastName,
                "address" => $this->faker->streetAddress,
                "city" => $this->faker->city,
                "state" => $this->faker->state,
                "postcode" => "6000",
                "country_id" => 1,
            ])
            ->assertStatus(403)
            ->assertJsonStructure(['message']);
    }

    /** @test */
    public function test_admin_update_lead_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/leads', [
                "escalation_level" => "Accept Or Decline",
                "escalation_status" => "Pending",
                "color" => "blue",
                "customer_type" => "Supply & Install",
                "house_type" => "2 storey house",
                "roof_preference" => "Tile",
                "commercial" => "test",
                "situation" => "test",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
                "comments" => "Test",
                "source" => "Newspaper",
                "staff_comments" => "Test",
                "sale_string" => "$0.00",
                "sale" => 0.00,
                "organisation_id" => $this->organisation->id,
                "email" => (substr($this->faker->uuid, -12, 12) . '_' . $this->faker->unique()->safeEmail),
                "contact_number" => $this->faker->e164PhoneNumber,
                "first_name" => $this->faker->firstName,
                "last_name" => $this->faker->lastName,
                "address" => $this->faker->streetAddress,
                "city" => $this->faker->city,
                "state" => $this->faker->state,
                "postcode" => "6000",
                "country" => "Australia",
                "use_for" => "Single Storey dwelling",
                "suburb" => $this->faker->state,
                "organisation_id" => $this->organisation->id,
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data']);

        $this->assertArrayHasKey('lead_escalation', $response['data']);
        $this->assertArrayHasKey('escalation_level', $response['data']['lead_escalation']);
        $this->assertArrayHasKey('escalation_status', $response['data']['lead_escalation']);
        $this->assertTrue($response['data']['lead_escalation']['escalation_level'] == 'Accept Or Decline');
        $this->assertTrue($response['data']['lead_escalation']['escalation_status'] == 'Pending');

        // It should update lead
        $response1 = $this->actingAs($this->admin)
            ->putJson('/api/v1/leads/' . $response['data']['id'] . '/', [
                "escalation_level" => "Accept Or Decline",
                "escalation_status" => "Declined",
                "color" => "red",
                "customer_type" => "Supply & Install",
                "house_type" => "2 storey house",
                "roof_preference" => "Tile",
                "commercial" => "test",
                "situation" => "test",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
                "comments" => "Test",
                "source" => "Newspaper",
                "staff_comments" => "Test",
                "sale_string" => "$0.00",
                "sale" => 0.00,
                "organisation_id" => $this->organisation->id,
                "email" => (substr($this->faker->uuid, -12, 12) . '_' . $this->faker->unique()->safeEmail),
                "contact_number" => $this->faker->e164PhoneNumber,
                "first_name" => $this->faker->firstName,
                "last_name" => $this->faker->lastName,
                "address" => $this->faker->streetAddress,
                "city" => $this->faker->city,
                "state" => $this->faker->state,
                "postcode" => "6000",
                "country" => "Australia",
                "use_for" => "Single Storey dwelling",
                "suburb" => $this->faker->state,
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data']);


        $this->assertArrayHasKey('lead_escalation', $response1['data']);
        $this->assertArrayHasKey('escalation_level', $response1['data']['lead_escalation']);
        $this->assertArrayHasKey('escalation_status', $response1['data']['lead_escalation']);
        $this->assertTrue($response1['data']['lead_escalation']['escalation_level'] == 'Accept Or Decline');
        $this->assertTrue($response1['data']['lead_escalation']['escalation_status'] == 'Declined');
    }

    /** @test */
    public function test_org_update_lead_escalation_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/leads', [
                "escalation_level" => "Accept Or Decline",
                "escalation_status" => "Pending",
                "color" => "blue",
                "customer_type" => "Supply & Install",
                "house_type" => "2 storey house",
                "roof_preference" => "Tile",
                "commercial" => "test",
                "situation" => "test",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
                "comments" => "Test",
                "source" => "Newspaper",
                "staff_comments" => "Test",
                "sale_string" => "$0.00",
                "sale" => 0.00,
                "organisation_id" => $this->organisation->id,
                "email" => (substr($this->faker->uuid, -12, 12) . '_' . $this->faker->unique()->safeEmail),
                "contact_number" => $this->faker->e164PhoneNumber,
                "first_name" => $this->faker->firstName,
                "last_name" => $this->faker->lastName,
                "address" => $this->faker->streetAddress,
                "city" => $this->faker->city,
                "state" => $this->faker->state,
                "postcode" => "6000",
                "country" => "Australia",
                "use_for" => "Single Storey dwelling",
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data']);

        $this->assertArrayHasKey('lead_escalation', $response['data']);
        $this->assertArrayHasKey('escalation_level', $response['data']['lead_escalation']);
        $this->assertArrayHasKey('escalation_status', $response['data']['lead_escalation']);
        $this->assertTrue($response['data']['lead_escalation']['escalation_level'] == 'Accept Or Decline');
        $this->assertTrue($response['data']['lead_escalation']['escalation_status'] == 'Pending');

        // It should update lead
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "lead_id" => $response['data']['id'],
                "escalation_level" => "Won",
                "escalation_status" => "Won",
                "reason" => "Lead has WON",
                "comments" => "Lead has WON",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data'])
            ->json();

        $this->assertTrue($response['data']['escalation_level'] == 'Won');
        $this->assertTrue($response['data']['escalation_status'] == 'Won');
    }

    /** @test */
    public function test_admin_delete_lead_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/leads', [
                "escalation_level" => "Accept Or Decline",
                "escalation_status" => "Pending",
                "color" => "blue",
                "customer_type" => "Supply & Install",
                "house_type" => "2 storey house",
                "roof_preference" => "Tile",
                "commercial" => "test",
                "situation" => "test",
                "gutter_edge_meters" => 123,
                "valley_meters" => 123,
                "comments" => "Test",
                "source" => "Newspaper",
                "staff_comments" => "Test",
                "sale_string" => "$0.00",
                "sale" => 0.00,
                "organisation_id" => $this->organisation->id,
                "email" => (substr($this->faker->uuid, -12, 12) . '_' . $this->faker->unique()->safeEmail),
                "contact_number" => $this->faker->e164PhoneNumber,
                "first_name" => $this->faker->firstName,
                "last_name" => $this->faker->lastName,
                "address" => $this->faker->streetAddress,
                "city" => $this->faker->city,
                "state" => $this->faker->state,
                "postcode" => "6000",
                "country" => "Australia",
                "use_for" => "Single Storey dwelling",
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data']);

        $this->assertArrayHasKey('lead_escalation', $response['data']);
        $this->assertArrayHasKey('escalation_level', $response['data']['lead_escalation']);
        $this->assertArrayHasKey('escalation_status', $response['data']['lead_escalation']);
        $this->assertTrue($response['data']['lead_escalation']['escalation_level'] == 'Accept Or Decline');
        $this->assertTrue($response['data']['lead_escalation']['escalation_status'] == 'Pending');

        // It should update lead
        $response1 = $this->actingAs($this->admin)
            ->deleteJson('/api/v1/leads/' . $response['data']['id'] . '/')
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function get_dashboard_by_administrator_success(){
        //with parameters organisation_id, escalation_level, keyword
        $response = $this->actingAs($this->admin)
            ->json('GET', '/api/v1/leads/getDashboard');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'data']);
        $response->json();
    }

    /** @test */
    public function get_dashboard_by_organisation_success(){
        $response = $this->actingAs($this->user)
            ->json('GET', '/api/v1/leads/getDashboard');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'data']);
        $response->json();
    }

    /** @test */
    public function get_dashboard_by_administrator_with_param_success(){
        //with parameters organisation_id, escalation_level, keyword
        $response = $this->actingAs($this->admin)
            ->json('GET', '/api/v1/leads/getDashboard', ['escalation_level' => 'Accept Or Decline', 'keyword' => 'Terrence']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'data']);
        $response->json();
    }

    /** @test */
    public function get_dashboard_by_organisation_with_param_success(){
        //with parameters organisation_id, escalation_level, keyword
        $response = $this->actingAs($this->user)
            ->json('GET', '/api/v1/leads/getDashboard', ['escalation_level' => 'Accept Or Decline']);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'data']);
        $response->json();
    }

    /** @test */
    public function get_dashboard_by_no_login_success(){
        $response = $this->json('GET', '/api/v1/leads/getDashboard', ['escalation_level' => 'Accept Or Decline']);

        $response->assertUnauthorized();
        $response->json();
    }
}
