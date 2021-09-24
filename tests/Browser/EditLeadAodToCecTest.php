<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Browser\Pages\Login;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;

class EditLeadAodToCecTest extends DuskTestCase
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
    public function edit_lead_aod_pending_to_cec_awaiting_response_success(){
        $this->browse(function (Browser $browser) {
            //login
            $browser->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->pause(1000)
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click td tr.
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //next button
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-update');

            $browser->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Awaiting Response', 'is_active' => 1]);

            $browser->screenshot('edit_lead_aod_pending_to_cec_awaiting_response_success');
        });
    }

    /** @test */
    public function edit_lead_aod_pending_to_cec_awaiting_response_email_sent_success(){
        $this->browse(function (Browser $browser) {
            //dashboard
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click td tr.
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //select status
            $browser->click('.escalation_status .el-input > .el-input__inner')
                ->waitFor('.escalation_status_popper')
                ->elements('.escalation_status_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > span > .el-select-dropdown__item')[1]->click();

            //next button
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-update');

            $browser->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Awaiting Response - Reminder Sent', 'is_active' => 1]);

            $browser->screenshot('edit_lead_aod_pending_to_cec_awaiting_response_email_sent_success');
        });
    }

    /** @test */
    public function edit_lead_aod_pending_to_cec_awaiting_response_admin_notified_success(){
        $this->browse(function (Browser $browser) {
            //dashboard
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

                $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

                //click td tr.
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //select status
            $browser->click('.escalation_status .el-input > .el-input__inner')
                ->waitFor('.escalation_status_popper')
                ->elements('.escalation_status_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > span > .el-select-dropdown__item')[2]->click();

            //next button
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-update');

            $browser->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Awaiting Response - Admin Notified', 'is_active' => 1]);

            $browser->screenshot('edit_lead_aod_pending_to_cec_awaiting_response_admin_notified_success');
        });
    }

    /** @test */
    public function edit_lead_aod_pending_to_cec_discontinued_success(){
        $this->browse(function (Browser $browser) {
            //dashboard
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

                $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click td tr.
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //select status
            $browser->click('.escalation_status .el-input > .el-input__inner')
                ->waitFor('.escalation_status_popper')
                ->elements('.escalation_status_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > span > .el-select-dropdown__item')[3]->click();

            //next button
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-update');

            $browser->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Discontinued', 'is_active' => 1]);

            $browser->screenshot('edit_lead_aod_pending_to_cec_discontinued_success');
        });
    }
}
