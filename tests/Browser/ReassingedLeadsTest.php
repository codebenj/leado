<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use LeadEscalationSeeder;
use OrganizationSeeder;
use Tests\Browser\Pages\Login;

class ReassingedLeadsTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $clearCookiesBetweenTests = true;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed');
        $this->seed(LeadEscalationSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->artisan('lada-cache:flush');
    }
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_reassinged_lead_success()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->fitContent();

            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click first table>tr in dashboard to show Lead Overview
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->click('.el-table__row');

            //check the drawer is display
            $browser->waitFor('.el-drawer__body')
                ->waitForText('Lead Overview')
                ->maximize();

            //click reassinged button
            $browser->click('@reassinged-lead-button');

            //check modal if show
            $browser//->waitFor('.el-dialog__wrapper');
                ->waitForText('Reassign Lead');

            //click first reasons
            $browser->click('.reasons .el-input > .el-input__inner')
                ->waitFor('.reasons_popper')
                ->elements('.reasons_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //organisation filter type
            $browser->click('.type_filter .el-input > .el-input__inner')
                ->waitFor('.type_filter_popper')
                ->elements('.type_filter_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //select organisation
            $browser->click('.organisation .el-input > .el-input__inner')
                ->waitFor('.select-org-search')
                ->elements('.select-org-search > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > span > .el-select-dropdown__item')[0]->click();

            //click sumbit button
            $browser->click('@reassigned-submit');

            //check sweet alert displaying success

            // $browser->waitFor('#swal2-content', 10)
            //     ->waitForText('Lead Reassigned Successfully.');
            $browser->waitFor('.swal2-popup')
                ->waitFor('.swal2-success');

            $browser->pause(1000);

            $browser->screenshot('reassinged_lead_success');
        });
    }
}
