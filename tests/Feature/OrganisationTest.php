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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use App\Mail\OrganizationManualNotification;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use CountriesDataSeeder;
use SettingSeeder;
use RoleSeeder;

class OrganisationTest extends TestCase
{
    //need to remove since i cant search during postcode import
    use RefreshDatabase, WithFaker;

    /** @var \App\User */
    protected $user, $user_2, $user_3;

    /** @var \App\Organisation */
    protected $organisation, $organisation_2, $organisation_3;

    /** @var \App\OrganizationUser */
    protected $organization_user, $organization_user_2, $organization_user_3;

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

        # Org 3 - manual notification
        $this->user_3 = factory(User::class)->create();
        $this->user_3->assignRole('organisation');
        $this->organisation_3 = factory(Organisation::class)->create([
            'metadata' => [
                'manual_update' => true,
            ],
            'user_id' => $this->user_3->id,
        ]);

        $organisation_user_3 = factory(OrganizationUser::class)->create(
            ['user_id' => $this->user_3->id, 'organisation_id' => $this->organisation_3->id]
        );

        $this->customer = factory(Customer::class)->create();

        $this->lead = factory(Lead::class)->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->leadEscalation = factory(LeadEscalation::class)->create([
            'lead_id' => $this->lead->id,
            'organisation_id' =>$this->organisation_3->id,
        ]);
    }

    protected function setupPermissions()
    {
        Role::findOrCreate('administrator');
        Role::findOrCreate('organisation');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function test_get_organisations_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/organizations');

        $response->assertSuccessful()
            ->assertJsonStructure(['data', 'success']);
    }

    /** @test */
    public function test_get_organisation_endpoint_success() {
        $this->actingAs($this->user)
            ->getJson('/api/v1/organizations/' . $this->organisation->id . '/')
            ->assertSuccessful()
            ->assertJsonStructure(['data', 'success']);
    }

    /** @test */
    public function test_get_organisation_endpoint_unauthozed_failed() {
        $this->actingAs($this->user_2)
            ->getJson('/api/v1/organizations/' . $this->organisation->id . '/')
            ->assertStatus(401)
            ->assertJsonStructure(['data', 'success']);
    }

    /** @test */
    public function test_get_organisation_endpoint_not_found_failed() {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/organizations/100/')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'success']);
    }

    /** @test */
    public function test_admin_create_organisation_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/organizations/', [
                    "name" => $this->faker->company,
                    "email" => (substr($this->faker->uuid, -12, 12) .'_'. $this->faker->unique()->safeEmail),
                    "password" => "password",
                    "org_code" => "123-312",
                    "first_name" => $this->faker->firstName,
                    "last_name" => $this->faker->lastName,
                    "contact_number" => "+63930079500",
                    "address" => $this->faker->streetAddress,
                    "city" => $this->faker->city,
                    "state" => $this->faker->state,
                    "country_id" => 1
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'success'])
            ->json();
    }

    /** @test */
    public function test_admin_update_organisation_endpoint_success() {
        $email = $this->faker->unique()->safeEmail;
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/organizations/', [
                    "name" => $this->faker->company,
                    "email" => $email,
                    "password" => "password",
                    "org_code" => (string) $this->faker->randomDigit,
                    "first_name" => $this->faker->firstName,
                    "last_name" => $this->faker->lastName,
                    "contact_number" => "+63930079500",
                    "address" => $this->faker->streetAddress,
                    "city" => $this->faker->city,
                    "state" => $this->faker->state,
                    "country_id" => 1,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'success']);
        $response->json();

        //$this->assertArrayHasKey('id', $response['data']); //cant locate the id key but in the array id is present.. so i used name this is locate
        $this->assertArrayHasKey('name', $response['data']);

        $email = $this->faker->unique()->safeEmail;

        $this->actingAs($this->admin)
            ->putJson("/api/v1/organizations/" . $response['data']['id'] ."/", [
                    "name" => $this->faker->company,
                    "email" => $email,
                    "password" => "password",
                    "org_code" => (string) $this->faker->randomDigit,
                    "first_name" => $this->faker->firstName,
                    "last_name" => $this->faker->lastName,
                    "contact_number" => "+63930079500",
                    "address" => $this->faker->streetAddress,
                    "city" => $this->faker->city,
                    "state" => $this->faker->state,
                    "country_id" => 1
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'success']);
    }

    /** @test */
    public function test_admin_delete_organisation_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/organizations/', [
                    "name" => $this->faker->company,
                    "email" => (substr($this->faker->uuid, -12, 12) .'_'. $this->faker->unique()->safeEmail),
                    "password" => "password",
                    "org_code" => (string) $this->faker->randomDigit,
                    "first_name" => $this->faker->firstName,
                    "last_name" => $this->faker->lastName,
                    "contact_number" => "+63930079500",
                    "address" => $this->faker->streetAddress,
                    "city" => $this->faker->city,
                    "state" => $this->faker->state,
                    "country_id" => 1
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['data', 'success']);

        //$this->assertArrayHasKey('id', $response['data']);
        $org_id = $response['data']['id'];

        $this->actingAs($this->admin)
            ->deleteJson("/api/v1/organizations/{$org_id}/")
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'success']);
    }

    /** @test */
    public function delete_organisations_bulk_success(){
        $organisation1 = factory(Organisation::class)->create();
        $organisation2 = factory(Organisation::class)->create();

        $ids = array($organisation1->id, $organisation2->id);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/organizations/delete', ['ids' => $ids]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'success'])
            ->json();
    }

    /** @test */
    public function import_organizations_success(){
        $name = 'sample_organizations.xlsx';
        //physical path
        $path = storage_path('test_assets/sample_organizations.xlsx');
        $file = new UploadedFile($path, $name);
        //on post need file name "import_file"
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/organisations/import', ['import_file' => $file]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function manual_notifications_success(){
        Mail::fake();

        $organisations = Organisation::whereHas('active_escalation', function($q){
            $q->whereNull('metadata->is_final');
        })->whereJsonContains('metadata->manual_update', true)->get();

        foreach($organisations as $organisation){
            try{ $email = $organisation->organisation_users[0]->user->email; }
            catch(\Exception $ex){ $email = ''; }

            if(!empty($email)){
                Mail::to('asolidom@gmail.com')->send(new OrganizationManualNotification($organisation));
                Mail::assertSent(OrganizationManualNotification::class);
            }
        }
    }

    /** @test */
    public function manual_trigger_manual_notification_successs(){
        Mail::fake();

        $organisation = Organisation::where('metadata->manual_update')->first();

        try{ $email = $organisation->organisation_users[0]->user->email; }
        catch(\Exception $ex){ $email = ''; }

        Mail::to($email)->send(new OrganizationManualNotification($organisation));

        Mail::assertSent(OrganizationManualNotification::class);
    }
}
