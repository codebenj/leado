<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Browser\Pages\Login;
use App\OrganizationUser;
use App\User;
use App\Organisation;

class OrganisationTest extends DuskTestCase
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
    public function organisation_create_success(){
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->maximize();

            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->fitContent();

            //go to organisation page
            $browser->visit('/admin/organisations')
                ->assertPathIs('/admin/organisations')
                ->waitUntilVue('pageTitle', 'Organisations', '@organisationPage')
                ->fitContent();

            //click add new organisation
            $browser->click('@addOrganisation')
                ->waitFor('.organisation-form')
                ->assertPathIs('/admin/organisations/create');


            //organasation information
            $browser->type('@org_name', $this->faker->name)
                ->type('@org_code', $this->faker->randomNumber)
                ->type('@org_first_name', $this->faker->firstName)
                ->type('@org_last_name', $this->faker->lastName)
                ->type('@org_email', $this->faker->safeEmail)
                ->type('@org_password', $this->faker->password)
                ->type('@org_suburb', $this->faker->word)
                ->type('@org_postcode', $this->faker->postcode);

            //fill up contact number
            $browser->type('#contact_number > .vti__input', '+639426771301');

            //fill state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //click save
            $browser->click('@organisation-save');

            //wait sweet alert
            // $browser->waitFor('#swal2-content');
            // $browser->waitForText('Organisation Successfully Inserted.');
            $browser->waitFor('.swal2-popup')
                ->waitFor('.swal2-success');

            $browser->screenshot('organisation_create_success');
        });
    }

    /** @test */
    public function organisation_duplicate_email_success(){
        $this->browse(function (Browser $browser) {
            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->fitContent();

            //go to organisation page
            $browser->visit('/admin/organisations')
                ->assertPathIs('/admin/organisations')
                ->waitUntilVue('pageTitle', 'Organisations', '@organisationPage')
                ->fitContent();

            //click add new organisation
            $browser->click('@addOrganisation')
                ->waitFor('.organisation-form')
                ->assertPathIs('/admin/organisations/create');


            //organasation information
            $browser->type('@org_name', $this->faker->name)
                ->type('@org_code', $this->faker->randomNumber)
                ->type('@org_first_name', $this->faker->firstName)
                ->type('@org_last_name', $this->faker->lastName)
                //fill up email address that is already exist, from seeder
                ->type('@org_email', 'organisation@traleado.com')
                ->type('@org_password', $this->faker->password)
                ->type('@org_suburb', $this->faker->word)
                ->type('@org_postcode', $this->faker->postcode);

            //fill up contact number
            $browser->type('#contact_number > .vti__input', '+639426771301');

            //fill state
            $browser->click('.state .el-input > .el-input__inner')
                ->waitFor('.state_popper')
                ->elements('.state_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

            //click save
            $browser->click('@organisation-save');

            //wait error field
            $browser->waitFor('.el-form-item__error');
            $browser->waitForText('The email has already been taken.');

            $browser->screenshot('organisation_duplicate_email_success');
        });
    }
}
