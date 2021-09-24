<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\CecLead;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;
use App\User;

class LeadEscalationInProgressTest extends DuskTestCase
{
    use DatabaseMigrations, WithFaker;

    public $organisation;
    public $lead;

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
    public function lead_escalation_in_progress_success(){
        $user = User::find($this->organisation->user_id);

        $this->browse(function (Browser $browser) use($user) {
            //login to dashboard
            $browser->visit(new Login)
                ->submit($user->email, 'password')
                ->pause(500)
                ->visit(new CecLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //input date
            $browser->type('.el-date-editor > .el-input__inner', date('Y-m-d', strtotime("+ 1 day")));

            //this lead is currently work in progress to hide calendar
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            $browser->screenshot('lead_escalation_in_progress_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_required_date_success(){
        $this->browse(function (Browser $browser) {
            $browser->visit(new CecLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.el-form-item__error')
                ->waitForText('Please select date');

            $browser->screenshot('lead_escalation_in_progress_required_date_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_date_input_exceed_date_success(){
        $this->browse(function (Browser $browser) {
            //login to dashboard
            $browser->visit(new CecLead)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //input date
            $browser->type('.el-date-editor > .el-input__inner', date('Y-m-d', strtotime("+7 months")));

            $browser->screenshot('lead_escalation_in_progress_date_input_exceed_date_success');

            //click to hide calendar
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            $browser->waitFor('.el-form-item__error')
                ->waitForText('Please select date');

            $browser->screenshot('lead_escalation_in_progress_date_input_exceed_date_notify_user_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_lost_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //select reasons for lost
            $browser->click('.lost_reasons .el-input > .el-input__inner')
                ->waitFor('.lost_reasons_popper')
                ->elements('.lost_reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //what-system
            $browser->type('@what-system', $this->faker->sentence);

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            $browser->screenshot('lead_escalation_in_progress_lost_success');
        });
    }


    /** @test */
    public function lead_escalation_in_progress_lost_must_select_reason_lost_required_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.el-form-item__error')
                ->waitForText('Please provide a reason. Thank you.');

            $browser->screenshot('lead_escalation_in_progress_lost_must_select_reason_lost_required_success');
        });
    }


    /** @test */
    public function lead_escalation_in_progress_lost_feeback_success(){
        /**
     * Enquirer went with a ski-slope system but a different installer.
     * Enquirer decided not to install ski-slope system because price too high.
     * Enquirer decided not to install ski-slope system because price too high.
     */
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //select reasons for lost
            $browser->click('.lost_reasons .el-input > .el-input__inner')
                ->waitFor('.lost_reasons_popper')
                ->elements('.lost_reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[1]->click();

            //extra-feedback
            $browser->type('@extra-feedback', $this->faker->sentence);

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            $browser->screenshot('lead_escalation_in_progress_lost_feeback_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_lost_other_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //select reasons for lost
            $browser->click('.lost_reasons .el-input > .el-input__inner')
                ->waitFor('.lost_reasons_popper')
                ->elements('.lost_reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[4]->click();

            //in-progress-indicate-reason
            $browser->type('@in-progress-indicate-reason', $this->faker->sentence);

            //extra-feedback
            $browser->type('@extra-feedback', $this->faker->sentence);

            $browser->screenshot('lead_escalation_in_progress_lost_other_success_with_inputs');

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            $browser->screenshot('lead_escalation_in_progress_lost_other_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_lost_other_indicate_reason_required_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[2]->click();

            //select reasons for lost
            $browser->click('.lost_reasons .el-input > .el-input__inner')
                ->waitFor('.lost_reasons_popper')
                ->elements('.lost_reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[4]->click();

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.el-form-item__error')
                ->waitForText('Please provide a reason. Thank you.');

            $browser->screenshot('lead_escalation_in_progress_lost_other_indicate_reason_required_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_won_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[3]->click();

            //gutter_edge_meters
            $browser->type('@gutter_edge_meters', $this->faker->randomFloat);

            //valley_meters
            $browser->type('@valley_meters', $this->faker->randomFloat);

            //date installation
            $browser->type('.el-date-editor > .el-input__inner', date('Y-m-d'));

            //click gutter to hide calendar
            $browser->click('@gutter_edge_meters');

            $browser->screenshot('lead_escalation_in_progress_won_success_with_inputs');

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            $browser->screenshot('lead_escalation_in_progress_won_success');
        });
    }

    /** @test */
    public function lead_escalation_in_progress_won_fields_required_success(){
        $this->browse(function (Browser $browser){
            //login to dashboard
            $browser->visit(new CecLead);

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $browser->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[3]->click();

            //update in progress
            $browser->click('@in-progress-submit');

            //wait the modal confirmation
            $browser->waitFor('.el-form-item__error');

            $browser->screenshot('lead_escalation_in_progress_won_fields_required_success');
        });
    }
}
