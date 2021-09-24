<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\AcceptLead;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;
use App\User;

class LeadEscalationCecTest extends DuskTestCase
{
    use DatabaseMigrations, WithFaker;

    protected $clearCookiesBetweenTests = true;

    public function setUp():void{
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed');
        $this->artisan('lada-cache:flush');

        factory(Customer::class, 1)
            ->create()
            ->each(function ($customer){
                $this->organisation = Organisation::inRandomOrder()->first();

                $this->lead = factory(Lead::class)->create([
                    'customer_id' => $customer->id,
                ]);

                factory(LeadEscalation::class)->create([
                    'lead_id' => $this->lead->id,
                    'organisation_id' => $this->organisation->id,
                ]);
            }
        );
    }

    /** @test */
    public function lead_escalation_cec_discountinue_success(){
        $user = User::find($this->organisation->user_id);

        $this->browse(function (Browser $browser) use($user) {
            //login to dashboard
            $browser->visit(new Login)
                ->submit($user->email, 'password')
                ->pause(500)
                ->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - discontinue
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //click reason - im on sick leave
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[1]->click();

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //click done to close modal
            //$browser->click('@confirmation-done');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_discountinue_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_discountinue_response_other_comments_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - discontinue
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //click reason - other
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[5]->click();

            //type comments
            $browser->type('@cec-comments', $this->faker->sentence);

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_discountinue_response_other_comments_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_discountinue_response_other_comments_required_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - discontinue
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //click reason - other
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[5]->click();

            //click save
            $browser->click('@cec-submit');

            // $browser->waitFor('.el-form-item__error')
            //     ->waitForText('Please provide a comment. Thank you.');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_discountinue_response_other_comments_required_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_contacted_the_enquirer_currently_work_in_progress_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have contacted the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[1]->click();

            //click reason - this lead is currently work in progress
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[0]->click();

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_contacted_the_enquirer_currently_work_in_progress_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_contacted_the_enquirer_currently_lost_different_system_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have contacted the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[1]->click();

            //click reason - this lead has been lost
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[2]->click();

            //select reasons for lost
            $browser->click('.lost_reasons .el-input > .el-input__inner')
                ->waitFor('.lost_reasons_popper')
                ->elements('.lost_reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_contacted_the_enquirer_currently_lost_different_system_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_contacted_the_enquirer_currently_lost_other_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have contacted the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[1]->click();

            //click reason - this lead has been lost
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[2]->click();

            //select reasons for lost
            $browser->click('.lost_reasons .el-input > .el-input__inner')
                ->waitFor('.lost_reasons_popper')
                ->elements('.lost_reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[4]->click();

            //indicate your reason
            $browser->type('@cec-indicate-reason', $this->faker->sentence);

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_contacted_the_enquirer_currently_lost_other_success');
        });
    }


    /** @test */
    public function lead_escalation_cec_contacted_the_enquirer_currently_won_and_installed_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have contacted the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[1]->click();

            //click reason - won and installed
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[3]->click();

            //gutter_edge_meters
            $browser->type('@gutter_edge_meters', $this->faker->randomFloat);

            //valley_meters
            $browser->type('@valley_meters', $this->faker->randomFloat);

            $browser->type('.el-date-editor > .el-input__inner', date('Y-m-d'));

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_contacted_the_enquirer_currently_won_and_installed_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_tried_contacted_the_enquirer_keep_trying_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have tried contact the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //click reason - waiting for their response
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[0]->click();

            //input date
            $browser->type('.el-date-editor > .el-input__inner', date('Y-m-d', strtotime("+ 1 day")));

            //click reason - will keep trying to remove the calendar
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[0]->click();

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_tried_contacted_the_enquirer_keep_trying_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_tried_contacted_the_enquirer_lead_is_lost_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have tried contact the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //click reason - lead is lost
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[2]->click();

            //type comments
            $browser->type('@cec-comments', $this->faker->sentence);

            //click save
            $browser->click('@cec-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_tried_contacted_the_enquirer_lead_is_lost_success');
        });
    }

    /** @test */
    public function lead_escalation_cec_tried_contacted_the_enquirer_lead_is_lost_comments_required_success(){
        $this->browse(function (Browser $browser){
            $browser->visit(new AcceptLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have tried contact the  enquirer
            $browser->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //click reason - lead is lost
            $browser->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[2]->click();

            //click save
            $browser->click('@cec-submit');

            // $browser->waitFor('.el-form-item__error')
            //     ->waitForText('Please provide a comment. Thank you.');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_cec_tried_contacted_the_enquirer_lead_is_lost_comments_required_success');
        });
    }
}
