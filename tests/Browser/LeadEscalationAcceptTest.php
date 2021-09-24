<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;
use App\User;

class LeadEscalationAcceptTest extends DuskTestCase
{
    use DatabaseMigrations, WithFaker;

    protected $clearCookiesBetweenTests = true;

    protected $organisation;

    protected $lead;

    public function setUp(): void
    {
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
    public function lead_escalation_accepted_success(){
        $user = User::find($this->organisation->user_id);

        $this->browse(function (Browser $browser) use($user) {
            //login to dashboard
            $browser->visit(new Login)
                ->submit($user->email, 'password')
                ->pause(1000)
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click accept
            $browser->click('@accepted-lead');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_accepted_success');
        });
    }

    /** @test */
    public function lead_escalation_declined_success(){
        $user = User::find($this->organisation->user_id);

        $this->browse(function (Browser $browser) use($user) {
            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click declined
            $browser->click('@declined-lead');

            //click first reasons
            $browser->click('.decline_reason .el-input > .el-input__inner')
                ->waitFor('.decline_reason_popper')
                ->elements('.decline_reason_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //click decline save
            $browser->click('@declined-lead-save');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_declined_success');
        });
    }

    /** @test */
    public function lead_escalation_declined_reason_other_comments_success(){
        $this->browse(function (Browser $browser){
            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click declined
            $browser->click('@declined-lead');

            //click first reasons
            $browser->click('.decline_reason .el-input > .el-input__inner')
                ->waitFor('.decline_reason_popper')
                ->elements('.decline_reason_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[5]->click();

            //comments
            $browser->waitFor('.el-textarea')
                ->type('@comments', 'comments sample');

            //click decline save
            $browser->click('@declined-lead-save');

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_declined_reason_other_comments_success');
        });
    }

    /** @test */
    public function lead_escalation_declined_reason_other_required_comments_success(){
        $this->browse(function (Browser $browser){
            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click declined
            $browser->click('@declined-lead');

            //click first reasons
            $browser->click('.decline_reason .el-input > .el-input__inner')
                ->waitFor('.decline_reason_popper')
                ->elements('.decline_reason_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[5]->click();

            //click decline save
            $browser->click('@declined-lead-save');

            //remove required comments
            //check Required comments message
            // $browser->waitFor('.el-form-item__error')
            //     ->waitForText('Please enter reason');

            //$browser->pause(500);

            $browser->screenshot('lead_escalation_declined_reason_other_required_comments_success');
        });
    }
}
