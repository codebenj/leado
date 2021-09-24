<?php

namespace Tests\Feature;

use App\User;
use App\LeadEscalation;
use App\Organisation;
use App\Customer;
use App\Lead;
use App\OrganizationUser;
use App\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use CountriesDataSeeder;
use SettingSeeder;
use App\Setting;
use RoleSeeder;

class NotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var \App\User */
    protected $user, $user_2;

    /** @var \App\Organisation */
    protected $organisation, $organisation_2;

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
            ['user_id' => $this->user_2->id, 'organisation_id' => $this->organisation_2->id],
        );
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
    public function test_admin_get_notifications_endpoint_success() {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/notifications')
            ->assertSuccessful()
            ->assertJsonStructure(['data'])
            ->json();

        $this->assertTrue(count($response['data']) > 0);
    }

    /** @test */
    public function test_admin_get_notification_message_success() {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/notifications')
            ->assertSuccessful()
            ->assertJsonStructure(['data'])
            ->json();
    }

    /** @test */
    public function admin_get_unread_notification_success(){
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/notifications/notification', ['is_read', 0])
            ->assertSuccessful()
            ->assertJsonStructure(['data'])
            ->json();
    }

    /** @test */
    public function cec_awaiting_response_email_sent_check_sms_success(){
        //create lead and assigned
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

        //accept leads (cec - awaiting response)
        $lead_id = $response['data']['id'];
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "comments" => "",
                "escalation_level" => "Confirm Enquirer Contacted",
                "escalation_status" => "Awaiting Response",
                "lead_id" => $lead_id,
                "reason" => "",
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data'])
        ->json();

        //update expiration date, and run crons to update lead status
        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();

        $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
        $countdown = Setting::where('name', $lead_escalation_status)->first();
        $expiration = Carbon::parse($lead_escalation->expiration_date);

        if($countdown && ! empty($countdown->value)) {
            $time_type = ucfirst($countdown->metadata['type']);
            $expiration = Date('Y-m-d H:i:s', strtotime('-'.$countdown->value." $time_type"));
        }

        $lead_escalation->expiration_date = $expiration;
        $lead_escalation->save();

        //cron
        $this->artisan('lead:escalation-level');
        $this->artisan('lada-cache:flush');

        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();

        //check in database
        $this->assertDatabaseHas('notifications', [
            'title' => 'AWAITING PROGRESS RESPONSE – REMINDER',
            'metadata->notification_type' => 'sms',
            'metadata->lead_id' => $lead_id,
        ]);

        //get notification
        $notifications = Notification::where(['title' => 'AWAITING PROGRESS RESPONSE – REMINDER', 'metadata->notification_type' => 'sms', 'metadata->lead_id' => $lead_id])->get();

        //check if Lead ID||Enquirer||Address is present in the description
        $is_found = false;
        foreach($notifications as $notification){
            $description = $notification->description;
            if(preg_match('/^(?=.*Lead ID)(?=.*Enquirer)(?=.*Address)/s', $description)) {
                $is_found = true;
            }else{
                $is_found = false;
                break;
            }
        }

        //assert to true if lead id, enquirer and address is found
        $this->assertTrue($is_found);
    }

    /** @test */
    public function cec_awaiting_response_admin_notified_check_sms_success(){
        //create lead and assigned
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

        //accept leads (cec - awaiting response)
        $lead_id = $response['data']['id'];
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "comments" => "",
                "escalation_level" => "Confirm Enquirer Contacted",
                "escalation_status" => "Awaiting Response",
                "lead_id" => $lead_id,
                "reason" => "",
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data'])
        ->json();

        //update expiration date, and run crons to update lead status
        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();
        $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
        $countdown = Setting::where('name', $lead_escalation_status)->first();
        $expiration = Carbon::parse($lead_escalation->expiration_date);

        if($countdown && ! empty($countdown->value)) {
            $time_type = ucfirst($countdown->metadata['type']);
            $expiration = Date('Y-m-d H:i:s', strtotime('-'.$countdown->value." $time_type"));
        }

        $lead_escalation->expiration_date = $expiration;
        $lead_escalation->save();

        //cron
        $this->artisan('lead:escalation-level');
        $this->artisan('lada-cache:flush');

        //check in database - email sent
        $this->assertDatabaseHas('notifications', [
            'title' => 'AWAITING PROGRESS RESPONSE – REMINDER',
            'metadata->notification_type' => 'sms',
            'metadata->lead_id' => $lead_id,
        ]);

        //update expiration date, and run crons to update lead status
        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();

        $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
        $countdown = Setting::where('name', $lead_escalation_status)->first();
        $expiration = Carbon::parse($lead_escalation->expiration_date);

        if($countdown && ! empty($countdown->value)) {
            $time_type = ucfirst($countdown->metadata['type']);
            $expiration = Date('Y-m-d H:i:s', strtotime('-'.$countdown->value." $time_type"));
        }

        $lead_escalation->expiration_date = $expiration;
        $lead_escalation->save();

        //cron
        $this->artisan('lead:escalation-level');
        $this->artisan('lada-cache:flush');

        //check in database - admin notified
        $this->assertDatabaseHas('notifications', [
            //'title' => 'ACCOUNT SUSPENDED – CONTACT ADMIN',
            'title' => 'UPDATE CRITICAL LEADS',
            'metadata->notification_type' => 'sms',
            'metadata->lead_id' => $lead_id,
        ]);

        //get notification
        //$notifications = Notification::where(['title' => 'ACCOUNT SUSPENDED – CONTACT ADMIN', 'metadata->notification_type' => 'sms', 'metadata->lead_id' => $lead_id])->get();
        $notifications = Notification::where(['title' => 'UPDATE CRITICAL LEADS', 'metadata->notification_type' => 'sms', 'metadata->lead_id' => $lead_id])->get();

        //check if Lead ID||Enquirer||Address is present in the description
        $is_found = false;
        foreach($notifications as $notification){
            $description = $notification->description;
            if(preg_match('/^(?=.*Lead ID)(?=.*Enquirer)(?=.*Address)/s', $description)) {
                $is_found = true;
            }else{
                $is_found = false;
                break;
            }
        }

        //assert to true if lead id, enquirer and address is found
        $this->assertTrue($is_found);
    }

    /** @test */
    public function in_progress_awaiting_response_email_sent_check_sms_success(){
        //create lead and assigned
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

        //update leads (cec - awaiting response)
        $lead_id = $response['data']['id'];
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "comments" => "",
                "escalation_level" => "Confirm Enquirer Contacted",
                "escalation_status" => "Awaiting Response",
                "lead_id" => $lead_id,
                "reason" => "",
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data'])
        ->json();

        //update leads (in-progess - awaiting response)
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "comments" => "test",
                "escalation_level" => "In Progress",
                "escalation_status" => "Awaiting Response",
                "gutter_edge_meters" => "",
                "indicate_reason" => "",
                "installation_date" => "",
                "lead_id" => $lead_id,
                "other_reason" => "",
                "progress_period_date" => "",
                "reason" => "This lead is currently Work in Progress",
                "response" => "I have contacted the Enquirer",
                "responseIndex" => 1,
                "tried_to_contact_date" => "",
                "valley_meters" => "",
                "what_system" => "",
            ]
        )
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data'])
        ->json();

        //update expiration date, and run crons to update lead status
        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();

        $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
        $countdown = Setting::where('name', $lead_escalation_status)->first();
        $expiration = Carbon::parse($lead_escalation->expiration_date);

        if($countdown && ! empty($countdown->value)) {
            $time_type = ucfirst($countdown->metadata['type']);
            $expiration = Date('Y-m-d H:i:s', strtotime('-'.$countdown->value." $time_type"));
        }

        $lead_escalation->expiration_date = $expiration;
        $lead_escalation->save();

        //cron
        $this->artisan('lead:escalation-level');
        $this->artisan('lada-cache:flush');

        //check in database - admin notified
        $this->assertDatabaseHas('notifications', [
            'title' => 'AWAITING PROGRESS RESPONSE – REMINDER',
            'metadata->notification_type' => 'sms',
            'metadata->lead_id' => $lead_id,
        ]);

        // //get notification
        $notifications = Notification::where(['title' => 'AWAITING PROGRESS RESPONSE – REMINDER', 'metadata->notification_type' => 'sms', 'metadata->lead_id' => $lead_id])->get();

        //check if Lead ID||Enquirer||Address is present in the description
        $is_found = false;
        foreach($notifications as $notification){
            $description = $notification->description;
            if(preg_match('/^(?=.*Lead ID)(?=.*Enquirer)(?=.*Address)/s', $description)) {
                $is_found = true;
            }else{
                $is_found = false;
                break;
            }
        }

        //assert to true if lead id, enquirer and address is found
        $this->assertTrue($is_found);
    }

    /** @test */
    public function in_progress_awaiting_response_admin_notifiy_check_sms_success(){
        //create lead and assigned
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

        //update leads (cec - awaiting response)
        $lead_id = $response['data']['id'];
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "comments" => "",
                "escalation_level" => "Confirm Enquirer Contacted",
                "escalation_status" => "Awaiting Response",
                "lead_id" => $lead_id,
                "reason" => "",
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data'])
        ->json();

        //update leads (in-progess - awaiting response)
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/leads/update_response', [
                "comments" => "test",
                "escalation_level" => "In Progress",
                "escalation_status" => "Awaiting Response",
                "gutter_edge_meters" => "",
                "indicate_reason" => "",
                "installation_date" => "",
                "lead_id" => $lead_id,
                "other_reason" => "",
                "progress_period_date" => "",
                "reason" => "This lead is currently Work in Progress",
                "response" => "I have contacted the Enquirer",
                "responseIndex" => 1,
                "tried_to_contact_date" => "",
                "valley_meters" => "",
                "what_system" => "",
            ]
        )
        ->assertSuccessful()
        ->assertJsonStructure(['success', 'data'])
        ->json();

        //update expiration date, and run crons to update lead status
        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();

        $lead_escalation_status = "{$lead_escalation->escalation_level} - {$lead_escalation->escalation_status}";
        $countdown = Setting::where('name', $lead_escalation_status)->first();
        $expiration = Carbon::parse($lead_escalation->expiration_date);

        if($countdown && ! empty($countdown->value)) {
            $time_type = ucfirst($countdown->metadata['type']);
            $expiration = Date('Y-m-d H:i:s', strtotime('-'.$countdown->value." $time_type"));
        }

        $lead_escalation->expiration_date = $expiration;
        $lead_escalation->save();

        //cron
        $this->artisan('lead:escalation-level');
        $this->artisan('lada-cache:flush');

        //check in database - admin notified
        $this->assertDatabaseHas('notifications', [
            'title' => 'AWAITING PROGRESS RESPONSE – REMINDER',
            'metadata->notification_type' => 'sms',
            'metadata->lead_id' => $lead_id,
        ]);

        //update expiration date, and run crons to update lead status
        $lead_escalation = LeadEscalation::where(['lead_id' => $lead_id, 'is_active' => 1])->first();
        $expiration = $lead_escalation->expiration_date->subDays(3);
        $lead_escalation->expiration_date = $expiration;
        $lead_escalation->save();

        \Log::info($lead_escalation);

        //cron
        $this->artisan('lead:escalation-level');
        $this->artisan('lada-cache:flush');

        //check in database - admin notified
        $this->assertDatabaseHas('notifications', [
            //'title' => 'ACCOUNT SUSPENDED – CONTACT ADMIN',
            'title' => 'AWAITING PROGRESS RESPONSE – REMINDER',
            'metadata->notification_type' => 'sms',
            'metadata->lead_id' => $lead_id,
        ]);

        // //get notification
        //$notifications = Notification::where(['title' => 'ACCOUNT SUSPENDED – CONTACT ADMIN', 'metadata->notification_type' => 'sms', 'metadata->lead_id' => $lead_id])->get();
        $notifications = Notification::where(['title' => 'AWAITING PROGRESS RESPONSE – REMINDER', 'metadata->notification_type' => 'sms', 'metadata->lead_id' => $lead_id])->get();

        //check if Lead ID||Enquirer||Address is present in the description
        $is_found = false;
        foreach($notifications as $notification){
            $description = $notification->description;
            if(preg_match('/^(?=.*Lead ID)(?=.*Enquirer)(?=.*Address)/s', $description)) {
                $is_found = true;
            }else{
                $is_found = false;
                break;
            }
        }

        //assert to true if lead id, enquirer and address is found
        $this->assertTrue($is_found);
    }
}
