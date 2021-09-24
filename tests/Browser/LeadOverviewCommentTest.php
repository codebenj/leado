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
use App\User;

class LeadOverviewCommentTest extends DuskTestCase
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
    public function lead_overview_comment_success(){

        $this->browse(function (Browser $browser) {
            //login
            $browser->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->pause(1000)
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click td tr.
            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click tab comments
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->elements('.el-tabs__item')[1]->click();

            //click button add comments
            $browser->click('@add-comment')
                ->type('@lead-comments-textarea', $this->faker->sentence)
                ->click('@lead-button-save');

            //wait modal for success
            $browser->waitFor('.swal2-popup ')
                ->waitFor('.swal2-success');

            $browser->screenshot('lead_overview_comment_success');
        });
    }

    /** @test */
    public function lead_overview_send_enquirer_success(){

        $this->browse(function (Browser $browser) {
            //dashboard
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click tab comments
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->elements('.el-tabs__item')[1]->click();

            //click button add comments
            $browser->click('@lead-overview-send-enquirer-notification')
                ->type('@send-notification', $this->faker->sentence)
                ->click('@send-notification-save');

            //wait modal for success
            $browser->waitFor('.swal2-popup ')
                ->waitFor('.swal2-success');

            $browser->screenshot('lead_overview_send_enquirer_success');
        });
    }
  
    /** @test */
    public function lead_overview_send_enquirer_success(){

        $this->browse(function (Browser $browser) {
            //login
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click tab comments
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if the form is loaded
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level Accept or Declined
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[4]->click();

            //next button
            $browser->click('@leadForm-next');

            //next button
            $browser->click('@leadForm-next');

            //next button
            $browser->click('@leadForm-next');

            $browser->click('@leadForm-update');

            //check sweetalert
            // $browser->waitFor('#swal2-content')
            //     ->waitForText('Lead successfully updated.');
            $browser->waitFor('.swal2-popup')
                ->waitFor('.swal2-success');

            $browser->waitFor('.swal2-popup')
                ->waitFor('.swal2-actions')
                ->click('.swal2-actions > .swal2-confirm');

            $browser->pause(1000);
          
            //fill up meters value
            $browser->waitFor('.metersForm')
                ->elements('.el-input-number__increase')[0]->click();

            $browser->waitFor('.metersForm')
                ->elements('.el-input-number__increase')[1]->click();

            //click save
            $browser->click('@meters-form-save');

            //wait modal for success
            $browser->waitFor('.swal2-popup')
                ->waitFor('.swal2-success');

            $browser->screenshot('lead_overview_send_enquirer_success');
        });
    }
}
