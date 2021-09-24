<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser as DuskBrowser;

class LeadGeneralInquiryTest extends DuskTestCase
{
    use DatabaseMigrations, WithFaker;

    protected $clearCookiesBetweenTests = true;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed');
        $this->artisan('lada-cache:flush');
    }

    /** @test */
    public function create_lead_general_inquiry_success(){
        $this->browse(function(Browser $browser){
            //login
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

            //customer type
            $browser->click('.customer_type .el-input > .el-input__inner')
                ->waitFor('.customer_type_popper')
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //fill in name and address
            $browser->type('@first_name', $this->faker->firstName)
                ->type('@last_name', $this->faker->lastName)
                ->type('@email', $this->faker->safeEmail)
                ->type('#contact_number > .vti__input', '+639426771301')
                ->type('@suburb', $this->faker->secondaryAddress)
                ->type('@postcode', $this->faker->postcode);

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //comments
            $browser->type('@general-inquiry-comments', 'test comments');

            //create botton
            $browser->click('@leadForm-save');

            //sweetalert
            $browser->waitFor('.swal2-success');

            //check sweetalert
            // $browser->waitFor('#swal2-content')
            //     ->waitForText('Lead was Successfully Created.');


            $browser->screenshot('create_lead_general_inquiry_success');
        });
    }

    /** @test */
    public function create_lead_general_inquiry_required_first_last_names_success(){
        $this->browse(function(Browser $browser){
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
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //fill in name and address
            $browser->type('@email', $this->faker->safeEmail)
                ->type('#contact_number > .vti__input', '+639426771301')
                ->type('@suburb', $this->faker->secondaryAddress)
                ->type('@postcode', $this->faker->postcode);

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //comments
            $browser->type('@general-inquiry-comments', 'test comments');

            //create botton
            $browser->click('@leadForm-save');

            //$browser->pause(10000000000);
            //check warning notifications
            $browser->waitFor('.el-form-item__error')
                ->waitForText('Please input first name');

            $browser->screenshot('create_lead_general_inquiry_required_first_last_names_success');
        });
    }

    /** @test */
    public function create_lead_general_inquiry_required_comments_success(){
        $this->browse(function(Browser $browser){
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
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[2]->click();

            //fill in name and address
            $browser->type('@first_name', $this->faker->firstName)
                ->type('@last_name', $this->faker->lastName)
                ->type('@email', $this->faker->safeEmail)
                ->type('#contact_number > .vti__input', '+639426771301')
                ->type('@suburb', $this->faker->secondaryAddress)
                ->type('@postcode', $this->faker->postcode);

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //create botton
            $browser->click('@leadForm-save');

            //remove since comments is not required
            //check warning notifications
            // $browser->waitFor('.el-form-item__error')
            //     ->waitForText('Comments is required');

            $browser->screenshot('create_lead_general_inquiry_required_comments_success');
        });
    }
}
