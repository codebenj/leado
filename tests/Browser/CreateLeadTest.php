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

class CreateLeadTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $admin;

    protected $clearCookiesBetweenTests = true;

    public function setUp(): void
    {
        parent::setUp();

        //keep this lines as reference next test page units
        // $this->seed(RoleSeeder::class);
        // $this->seed(OrganizationSeeder::class);
        // $this->app->make(PermissionRegistrar::class)->registerPermissions();
        // $this->admin = factory(User::class)->create();
        // $this->admin->assignRole('administrator');

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed');
        $this->artisan('lada-cache:flush');
    }

    /** @test */
    public function create_leads_required_fields_first_step_success(){
        $this->browse(function (Browser $browser) {

            $browser->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->maximize();

            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //lead create page
            $browser->visit('/admin/leads/create')
                ->assertPathIs('/admin/leads/create')
                ->waitUntilVue('pageTitle', 'New Enquiry', '@leadForm')
                ->maximize();

            //next button
            $browser->click('@leadForm-next');

            $browser->waitFor('.el-form-item__error');

            $browser->waitForText('Please select lead type');

            $browser->screenshot('create_leads_required_fields_first_step_success');
        });
    }


    /** @test */
    public function create_leads_organisation_required_success()
    {
        $this->browse(function (Browser $browser) {
            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //lead create page
            $browser->visit('/admin/leads/create')
                ->assertPathIs('/admin/leads/create')
                ->waitUntilVue('pageTitle', 'New Enquiry', '@leadForm')
                ->maximize();

            //customer type
            $browser->click('.customer_type .el-input > .el-input__inner')
                ->waitFor('.customer_type_popper')
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //select level Accept or Declined
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[1]->click();

            //fill in name and address
            $browser->type('@first_name', 'Norman')
                ->type('@last_name', 'Centillas')
                ->type('@email', 'asolidom@gmail.com')
                ->type('@address', '29-2 Bonifacio St.')
                ->type('@city', 'Davao City')
                ->type('@suburb', 'Suburb')
                ->type('@postcode', '8000');

            //fill up contact number
            $browser->type('#contact_number > .vti__input', '+639426771301');

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //next button
            $browser->click('@leadForm-next');

            //house type
            $browser->click('@house_type_single_storey_dwelling');

            //preference type
            $browser->click('@roof_preference_tile');

            //enquirer position / use_for
            $browser->click('@use_for_i_am_a_homeowner');

            //gutter_edge_meters and valley_meters
            $browser->type('@gutter_edge_meters', 44)
                ->type('@valley_meters', 33);

            //Marketing channel
            $browser->click('@source_searched_on_the_internet');

            //additional_information
            $browser->type('@additional_information', 'text message');

            //next botton
            $browser->click('@leadForm-next');

            //enquiry recieved
            $browser->click('.received_via .el-input > .el-input__inner')
                ->waitFor('.received_via_popper')
                ->elements('.received_via_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //staff_comments
            $browser->type('@staff_comments', 'staff comments');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-save');

            $browser->waitFor('.swal2-error');
            //$browser->waitFor('.swal2-success');

            // $browser->waitFor('#swal2-content');
            // $browser->waitForText('Organisation is required to save this lead.');

            $browser->screenshot('create_leads_organisation_required_success');
        });

    }


    /** @test */
    public function create_leads_success()
    {
        $this->browse(function (Browser $browser) {

            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            //lead create page
            $browser->visit('/admin/leads/create')
                ->assertPathIs('/admin/leads/create')
                ->waitUntilVue('pageTitle', 'New Enquiry', '@leadForm')
                ->maximize();

            //customer type
            $browser->click('.customer_type .el-input > .el-input__inner')
                ->waitFor('.customer_type_popper')
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //select level Accept or Declined
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //fill in name and address
            $browser->type('@first_name', 'Norman')
                ->type('@last_name', 'Centillas')
                ->type('@email', 'asolidom@gmail.com')
                ->type('@address', '29-2 Bonifacio St.')
                ->type('@city', 'Davao City')
                ->type('@suburb', 'Suburb')
                ->type('@postcode', '8000');

            //fill up contact number
            $browser->type('#contact_number > .vti__input', '+639426771301');

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //next button
            $browser->click('@leadForm-next');

            //house type
            $browser->click('@house_type_single_storey_dwelling');

            //preference type
            $browser->click('@roof_preference_tile');

            //enquirer position / use_for
            $browser->click('@use_for_i_am_a_homeowner');

            //gutter_edge_meters and valley_meters
            $browser->type('@gutter_edge_meters', 44)
                ->type('@valley_meters', 33);

            //Marketing channel
            $browser->click('@source_searched_on_the_internet');

            //additional_information
            $browser->type('@additional_information', 'text message');

            //next botton
            $browser->click('@leadForm-next');

            //enquiry recieved
            $browser->click('.received_via .el-input > .el-input__inner')
                ->waitFor('.received_via_popper')
                ->elements('.received_via_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //staff_comments
            $browser->type('@staff_comments', 'staff comments');

            //next botton
            $browser->click('@leadForm-next');

            //organisation filter
            $browser->click('.organisation_filter .el-input > .el-input__inner')
                ->waitFor('.organisation_filter_popper')
                ->elements('.organisation_filter_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //organisation select
            $browser->click('.organisation .el-input > .el-input__inner')
                ->waitFor('.select-org-search')
                ->elements('.select-org-search > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //create botton
            $browser->click('@leadForm-save');

            //$browser->pause(100000000000);
            //check sweetalert
          
            // $browser->waitFor('#swal2-content')
            //     ->waitForText('Lead was Successfully Created.');

            $browser->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Pending', 'is_active' => 1]);

            $browser->screenshot('create_leads_success');
        });

    }
}
