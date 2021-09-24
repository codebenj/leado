<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Mail;
use App\Mail\OrganizationNotification;
use App\LeadEscalation;
use App\Notification;
use App\Organisation;
use App\Customer;
use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use SMSGlobal\Credentials;
use SMSGlobal\Exceptions\AuthenticationException;
use SMSGlobal\Exceptions\CredentialsException;
use SMSGlobal\Exceptions\InvalidPayloadException;
use SMSGlobal\Exceptions\InvalidResponseException;
use SMSGlobal\Exceptions\ResourceNotFoundException;
use SMSGlobal\Resource\Sms;
use RoleSeeder;
use App\OrganizationUser;
use CountriesDataSeeder;
use App\Lead;
use App\User;
use SettingSeeder;


class SmsEmailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $credentials;
    protected $apiKey = 'd0337b4987673915b305bb6443e028e7';
    protected $apiSecret = 'eacc15ad23ba11fd7acf62847f3cc1ac';

    /** @var \App\User */
    protected $user;

    /** @var \App\Organisation */
    protected $organisation;

    /** @var \App\LeadEscalation */
    protected $leadEscalation;

    protected $lead;

    protected $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
        $this->seed(CountriesDataSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->user = factory(User::class)->create();
        $this->user->assignRole('organisation');

        $this->organisation = factory(Organisation::class)->create([
            'user_id' => $this->user->id,
        ]);

        factory(OrganizationUser::class)->create(
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

        $this->credentials =  Credentials::set($this->apiKey, $this->apiSecret);
    }

    /** @test */
    public function email_organisation_escalation_success()
    {
        Mail::fake();

        $notification = new Notification();

        $lead_escalation = $this->leadEscalation;

        $notification_message = $notification->parseMessages();

        Mail::to('asolidom@gmail.com')->send(new OrganizationNotification($lead_escalation, $notification_message));

        Mail::assertSent(OrganizationNotification::class);
    }

    /** @test */
    public function testSendToOne(): void
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateString = $date->format('Y-m-d H:i:s O');

        $responseBody = '{"messages":[{"id":"123","outgoing_id":1,"origin":"origin","destination":"+639426771301","message":"This is a test message","status":"sent","dateTime":"' . $dateString . '"}]}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json'], $responseBody)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        try {
            $sms = new Sms($client);
            $smsResponse = $sms->sendToOne('+639426771301', 'This is a test message');
            $this->assertEquals('123', $smsResponse['messages'][0]['id']);
        } catch (GuzzleException $e) {
            $this->fail('This test should not have failed');
        } catch (AuthenticationException $e) {
            $this->fail('This test should not have failed');
        } catch (InvalidPayloadException $e) {
            $this->fail('This test should not have failed');
        } catch (InvalidResponseException $e) {
            $this->fail('This test should not have failed');
        } catch (ResourceNotFoundException $e) {
            $this->fail('This test should not have failed');
        } catch (CredentialsException $e) {
            $this->fail('This test should not have failed');
        }
    }
}
