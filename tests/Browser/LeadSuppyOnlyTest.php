<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Illuminate\Foundation\Testing\WithFaker;

class LeadSuppyOnlyTest extends DuskTestCase
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
    public function create_lead_supply_only_success(){
        $this->browse(function (Browser $browser) {
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
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[1]->click();

            //fill in name and address
            $browser->type('@first_name', $this->faker->firstName)
                ->type('@last_name', $this->faker->lastName)
                ->type('@email', $this->faker->safeEmail)
                ->type('#contact_number > .vti__input', '+639426771301')
                ->type('@address', $this->faker->streetAddress)
                ->type('@city', $this->faker->city)
                ->type('@suburb', $this->faker->secondaryAddress)
                ->type('@postcode', $this->faker->postcode);

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //next botton
            $browser->click('@leadForm-next');

            //house type
            $browser->click('@house_type_single_storey_dwelling');

            //preference type
            $browser->click('@roof_preference_tile');

            //enquirer position / use_for
            $browser->click('@use_for_i_am_a_tradesperson_builder');

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

            $browser->type('@staff_comments', 'text message');

            //create botton
            $browser->click('@leadForm-save');

            //check sweetalert
            // $browser->waitFor('#swal2-content')
            //     ->waitForText('Lead was Successfully Created.');
            $browser->waitFor('.swal2-popup')
                ->waitFor('.swal2-success');

            $browser->screenshot('create_lead_supply_only_success');
        });
    }

    /** @test */
    public function create_lead_supply_only_required_email_address_success(){
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
                ->elements('.customer_type_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[1]->click();

            //fill in name and address
            $browser->type('@first_name', $this->faker->firstName)
                ->type('@last_name', $this->faker->lastName)
                //->type('@email', $this->faker->safeEmail)
                ->type('#contact_number > .vti__input', '+639426771301')
                ->type('@address', $this->faker->streetAddress)
                ->type('@city', $this->faker->city)
                ->type('@suburb', $this->faker->secondaryAddress)
                ->type('@postcode', $this->faker->postcode);

            //fill up state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //next botton
            $browser->click('@leadForm-next');

            //check warning notifications
            $browser->waitFor('.el-form-item__error')
                ->waitForText('Please input email address');

            $browser->screenshot('create_lead_supply_only_required_email_address_success');
        });
    }
}
