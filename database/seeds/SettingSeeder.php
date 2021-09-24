<?php

use Illuminate\Database\Seeder;
use App\Setting;
class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'unassigned-unassigned',
                'name' => 'Unassigned - Unassigned',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Unassigned',
                    'status' => 'Unassigned',
                    'admin_tooltip' => 'This lead was produced by a Web Form. It needs to be assigned to an Organisation.',
                    'org_tooltip' => '',
                ],
            ],

            [
                'key' => 'unassigned-special-opportunity',
                'name' => 'Unassigned - Special Opportunity',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Unassigned',
                    'status' => 'Special Opportunity',
                    'admin_tooltip' => 'This lead has been identified as a possible Special Opportunity. It will need to be assigned appropriately.',
                    'org_tooltip' => '',
                ],
            ],

            [
                'key' => 'aod-pending',
                'name' => 'Accept Or Decline - Pending',
                'value' => '2',
                'metadata' => [
                    'type' => 'hours',
                    'level' => 'Accept Or Decline',
                    'status' => 'Pending',
                    'admin_tooltip' => 'If the Status is not updated within this time the lead will lapse.',
                    'org_tooltip' => 'If the Status is not updated within this time the lead will lapse and your account will be suspended from receiving new leads.',
                ],
            ],

            [
                'key' => 'aod-declined',
                'name' => 'Accept Or Decline - Declined',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Accept Or Decline',
                    'status' => 'Declined',
                    'admin_tooltip' => 'This lead was DECLINED by the Organisation - [reason]',
                    'org_tooltip' => 'You have DECLINED this lead - [reason]',
                ],
            ],

            [
                'key' => 'aod-declined-lapsed',
                'name' => 'Accept Or Decline - Declined-Lapsed',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Accept Or Decline',
                    'status' => 'Declined-Lapsed',
                    'admin_tooltip' => 'No response. Account has been suspended and this lead should be reassigned.',
                    'org_tooltip' => 'The Status was not updated in time. Your account has been suspended from receiving new leads and the Admin has been notified.',
                ],
            ],

            [
                'key' => 'cec-awaiting-response',
                'name' => 'Confirm Enquirer Contacted - Awaiting Response',
                'value' => '2',
                'metadata' => [
                    'type' => 'hours',
                    'level' => 'Confirm Enquirer Contacted',
                    'status' => 'Awaiting Response',
                    'admin_tooltip' => 'If the Status is not updated within this time a reminder email will be sent.',
                    'org_tooltip' => 'Please update the status of this lead.',
                ],
            ],

            [
                'key' => 'cec-awaiting-response-tried',
                'name' => 'Confirm Enquirer Contacted - Awaiting Response - Tried Contacting',
                'value' => '12',
                'metadata' => [
                    'type' => 'hours',
                    'level' => 'Confirm Enquirer Contacted',
                    'status' => 'Awaiting Response',
                    'admin_tooltip' => 'If the Status is not updated within this time a reminder email will be sent.',
                    'org_tooltip' => 'Please update the status of this lead.',
                ],
            ],

            [
                'key' => 'cec-awaiting-response-email-sent',
                'name' => 'Confirm Enquirer Contacted - Awaiting Response - Reminder Sent',
                'value' => '24',
                'metadata' => [
                    'type' => 'hours',
                    'level' => 'Confirm Enquirer Contacted',
                    'status' => 'Awaiting Response - Reminder Sent',
                    'admin_tooltip' => 'A reminder email has been sent to the Organisation. If they don\'t update the Status within this time their account will be suspended and the Admin will be notified.',
                    'org_tooltip' => 'Please update the status of this lead immediately.',
                ],
            ],

            [
                'key' => 'cec-awaiting-response-admin-notified',
                'name' => 'Confirm Enquirer Contacted - Awaiting Response - Admin Notified',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Confirm Enquirer Contacted',
                    'status' => 'Awaiting Response - Admin Notified',
                    'admin_tooltip' => 'The Organisation is unresponsive. The account has been suspended and the Admin has been notified. This lead may need to be reassigned.',
                    'org_tooltip' => 'The Status was not updated in the given time. Your account has been suspended from receiving new leads and the Admin has been notified.',
                ],
            ],

            [
                'key' => 'cec-discontinued',
                'name' => 'Confirm Enquirer Contacted - Discontinued',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Confirm Enquirer Contacted',
                    'status' => 'Discontinued',
                    'admin_tooltip' => 'This lead was DISCONTINUED by the Organisation - [reason]',
                    'org_tooltip' => 'You have DISCONTINUED this lead - [reason]',
                ],
            ],

            [
                'key' => 'cec-declined',
                'name' => 'Confirm Enquirer Contacted - Declined',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Confirm Enquirer Contacted',
                    'status' => 'Declined',
                    'admin_tooltip' => 'This lead was DECLINED by the Organisation - [reason]',
                    'org_tooltip' => 'You have DECLINED this lead - [reason]',
                ],
            ],

            [
                'key' => 'inprogress-awaiting-response',
                'name' => 'In Progress - Awaiting Response',
                'value' => '24',
                'metadata' => [
                    'type' => 'days',
                    'level' => 'In Progress',
                    'status' => 'Awaiting Response',
                    'admin_tooltip' => 'If the Status is not updated within this time a reminder email will be sent.',
                    'org_tooltip' => 'Please update the status of this lead.',
                ],
            ],

            [
                'key' => 'inprogress-awaiting-response-email-sent',
                'name' => 'In Progress - Awaiting Response - Reminder Sent',
                'value' => '48',
                'metadata' => [
                    'type' => 'hours',
                    'level' => 'In Progress',
                    'status' => 'Awaiting Response - Reminder Sent',
                    'admin_tooltip' => 'A reminder email has been sent to the Organisation. If they don\'t update the Status within this time their account will be suspended and the Admin will be notified.',
                    'org_tooltip' => 'Please update the status of this lead immediately.',
                ],
            ],

            [
                'key' => 'inprogress-awaiting-response-admin-notified',
                'name' => 'In Progress - Awaiting Response - Admin Notified',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'In Progress',
                    'status' => 'Awaiting Response - Admin Notified',
                    'admin_tooltip' => 'The Organisation is unresponsive. The account has been suspended and the Admin has been notified. This lead may need to be reassigned.',
                    'org_tooltip' => 'The Status was not updated in the given time. Your account has been suspended from receiving new leads and the Admin has been notified.',
                ],
            ],

            [
                'key' => 'inprogress-discontinued',
                'name' => 'In Progress - Discontinued',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'In Progress',
                    'status' => 'Discontinued',
                    'admin_tooltip' => 'This lead was DISCONTINUED by the Organisation - [reason]',
                    'org_tooltip' => 'You have DISCONTINUED this lead - [reason]',
                ],
            ],

            [
                'key' => 'inprogress-extended',
                'name' => 'In Progress - Extended',
                'value' => '6',
                'metadata' => [
                    'type' => 'months',
                    'level' => 'In Progress',
                    'status' => 'Extended',
                    'admin_tooltip' => 'A reminder email has been sent to the Organisation. If they don\'t update the Status in this time their account will be suspended and the Admin will be notified.',
                    'description' => '',
                ],
            ],

            [
                'key' => 'inprogress-extended-1',
                'name' => 'In Progress - Extended 1',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'In Progress',
                    'status' => 'Extended 1',
                    'admin_tooltip' => 'The Organisation has extended the progress of this lead. If they don\'t update the status in this time, the Horizontal Escalation will begin.',
                    'org_tooltip' => 'Please update the Status of this lead within this time.',
                ],
            ],

            [
                'key' => 'inprogress-extended-2',
                'name' => 'In Progress - Extended 2',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'In Progress',
                    'status' => 'Extended 2',
                    'admin_tooltip' => 'The Organisation has extended the progress of this lead. If they don\'t update the status in this time, the Horizontal Escalation will begin.',
                    'org_tooltip' => 'Please update the Status of this lead within this time.',
                ],
            ],

            [
                'key' => 'inprogress-extended-3',
                'name' => 'In Progress - Extended 3',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'In Progress',
                    'status' => 'Extended 3',
                    'admin_tooltip' => 'The Organisation has extended the progress of this lead. If they don\'t update the status in this time, the Horizontal Escalation will begin.',
                    'org_tooltip' => 'Please update the Status of this lead within this time.',
                ],
            ],

            [
                'key' => 'discontinued-discontinued',
                'name' => 'Discontinued - Discontinued',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Discontinued',
                    'status' => 'Discontinued',
                    'admin_tooltip' => 'This lead was DISCONTINUED by the Organisation - [reason]',
                    'org_tooltip' => 'You have DISCONTINUED this lead - [reason]',
                ],
            ],

            [
                'key' => 'abandoned-abandoned',
                'name' => 'Abandoned - Abandoned',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Abandoned',
                    'status' => 'Abandoned',
                    'admin_tooltip' => 'This lead was ABANDONED by the Organisation. It has been reassigned to another Organisaiton.',
                    'org_tooltip' => 'You have ABANDONED this lead.',
                ],
            ],

            [
                'key' => 'lost-lost',
                'name' => 'Lost - Lost',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Lost',
                    'status' => 'Lost',
                    'admin_tooltip' => 'This lead was LOST - [reason]. The Admin has been notified.',
                    'org_tooltip' => 'This lead was LOST - [reason]',
                ],
            ],

            [
                'key' => 'won-won',
                'name' => 'Won - Won',
                'value' => '',
                'metadata' => [
                    'type' => '',
                    'level' => 'Won',
                    'status' => 'Won',
                    'admin_tooltip' => 'Congratulations',
                    'org_tooltip' => 'Congratulations',
                ],
            ],

            [
                'key' => 'manual-notifications-organisation',
                'name' => 'Manual Notification Organisation',
                'value' => '1, 09:00',
                'metadata' => [
                    'day' => 1,
                    'hour' => '09',
                    'type' => '',
                    'am_pm' => 'AM',
                    'level' => '',
                    'minute' => '00',
                    'timezone' => '2021-08-02T09:00:00+06:00',
                    'description' => '',
                    'org_tooltip' => 'Day and time when the notification will be sent to organisation',
                    'admin_tooltip' => 'Day and time when the notification will be sent to organisation',
                    'timezone_name' => 'Australia/Perth'
                ],
            ],

            [
                'key' => 'company-name',
                'name' => 'Company Name',
                'value' => 'Traleado',
                'metadata' => [
                    'day' => '1',
                    'time' => '',
                    'type' => '',
                    'level' => '',
                    'status' => '',
                    'admin_tooltip' => '',
                    'org_tooltip' => '',
                ],
            ],

            [
                'key' => 'admin-email-notification-receivers',
                'name' => 'Admin Email Notification Receivers',
                'value' => 'taylordavisqld@gmail.com',
                'metadata' => [
                    'type' => '',
                    'level' => '',
                    'status' => '',
                ],
            ],

            [
                'key' => 'admin-enquire-message',
                'name' => 'Admin Enquire Message Template',
                'value' => 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au',
                'metadata' => [
                    'type' => '',
                    'level' => '',
                    'status' => '',
                ],
            ],

        ];

        Setting::truncate();

        foreach($settings as $setting) {
            $setting_model = new Setting;
            $setting_model->key = $setting['key'];
            $setting_model->name = $setting['name'];
            $setting_model->value = $setting['value'];
            $setting_model->metadata = $setting['metadata'];
            $setting_model->save();
        }
    }
}
