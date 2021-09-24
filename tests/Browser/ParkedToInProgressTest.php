<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;
use RoleSeeder;
use AdminSeeder;
use OrganizationSeeder;
use Tests\Browser\Pages\CreateLead;
use Tests\Browser\Pages\Login;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;

class ParkedToInProgressTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $admin;

    protected $organisation;

    protected $lead;

    protected $clearCookiesBetweenTests = true;

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
    public function parked_inprogress_lead_success(){
        $this->browse(function ($admin, $org) {
            //login
            $admin->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->maximize();

            //dashboard
            $admin->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //login
            $org->visit(new Login)
                ->submit( $this->organisation->user->email, 'password')
                ->maximize();

            //dashboard
            $org->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //click action dropdown to update lead
            $org->waitFor('.sc-table')
            ->waitFor('.el-table__body-wrapper')
            ->waitFor('.el-table__body')
            ->mouseover('.el-table__row')
            ->click('.drop-down-action-organization')
            ->waitFor('.action-popper')
            ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click accept
            $org->click('@accepted-lead');

            //wait the modal confirmation
            $org->waitFor('.escalation-modal')
                ->waitForText('Confirmation');

            //click done to close modal
            $org->click('@confirmation-done');

            //click action dropdown to update lead
            $org->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //click responses - i have contacted the  enquirer
            $org->waitFor('@cecForm')
                ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[1]->click();

            //click reason - this lead is currently work in progress
            $org->waitFor('@cecForm')
                ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[0]->click();

            //click save
            $org->click('@cec-submit');

            //wait the modal confirmation
            $org->waitFor('.escalation-modal')
                ->waitForText('Confirmation')
                ->click('@confirmation-done');


            //click td tr.
            $admin->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $admin->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $admin->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select status
            $admin->click('.escalation_status .el-input > .el-input__inner')
                ->waitFor('.escalation_status_popper')
                ->elements('.escalation_status_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > span > .el-select-dropdown__item')[4]->click();

            //next button
            $admin->click('@leadForm-next');

            //next botton
            $admin->click('@leadForm-next');

            //next botton
            $admin->click('@leadForm-next');

            //create botton
            $admin->click('@leadForm-update')
                ->pause(1000);

            $admin->waitFor('.swal2-actions')
                ->click('.swal2-confirm');

            //click action dropdown to update lead
            $org->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //this lead is currently work in progress
            $org->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //input date
            $org->type('.el-date-editor > .el-input__inner', date('Y-m-d', strtotime("+ 5 day")));

            //this lead is currently work in progress to hide calendar
            $org->waitFor('@inProgressForm')
                ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[0]->click();

            //update in progress
            $org->click('@in-progress-submit');

            //wait the modal confirmation
            $org->waitFor('.escalation-modal')
                ->waitForText('Confirmation')
                ->click('@confirmation-done');

            $admin->waitFor('.el-drawer__body')
                ->click('.drawerClose');

            $this->assertDatabaseHas('lead_escalations', ['escalation_level' => 'In Progress', 'is_active' => 1, 'reason' => 'This lead is currently Work in Progress']);

            $admin->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->waitFor('.escalation');
                //  ->waitFor('.d-block');


            // $admin->pause(10000000000);
            // $org->pause(10000000000);
        });
    }
}
