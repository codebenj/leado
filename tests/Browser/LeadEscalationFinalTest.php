<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\Declined;
use Tests\Browser\Pages\Discontinue;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;
use App\User;
use Tests\Browser\Pages\Won;
use Tests\Browser\Pages\Lost;

class LeadEscalationFinalTest extends DuskTestCase
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
    public function lead_escalation_final_declined_success(){
        $user = User::find($this->organisation->user_id);

        $this->browse(function (Browser $browser) use($user) {
            //login to dashboard
            $browser->visit(new Login)
                ->submit($user->email, 'password')
                ->pause(500)
                ->visit(new Declined)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //wait the modal confirmation
            $browser->waitFor('.escalation-modal')
                ->waitForText('THIS LEAD IS NO LONGER AVAILABLE. IT WAS NOT ACCEPTED IN TIME AND HAS THEREFORE BEEN RECORDED AS DECLINED.');

            $browser->screenshot('lead_escalation_final_declined_success');
        });
    }

    /** @test */
    public function lead_escalation_final_declined_lapse_success(){
        $this->browse(function (Browser $browser) {
            //dashboard page
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            $lead_escalation = LeadEscalation::where(['lead_id' => $this->lead->id, 'is_active' => 1])->first();

            //update expiration date to(minus 2 hrs to execute in updates)
            $expiration = $lead_escalation->expiration_date->subHours(3);
            $lead_escalation->expiration_date = $expiration;
            $lead_escalation->save();

            //run cron jobs to update escalation leads
            $this->artisan('lead:escalation-level');
            $this->artisan('lada-cache:flush');

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //wait the modal confirmation
            $browser->waitFor('.el-dialog__body')
                ->waitForText('THIS LEAD IS NO LONGER AVAILABLE. IT WAS NOT ACCEPTED IN TIME AND HAS THEREFORE BEEN RECORDED AS DECLINE-LAPSED.');

            $browser->screenshot('lead_escalation_final_declined_lapse_success');
        });
    }

    /** @test */
    public function lead_escalation_final_discontinued_success(){
        $this->browse(function (Browser $browser) {
            //dashboard page
            $browser->visit(new Discontinue)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //wait the modal confirmation
            $browser->waitFor('.el-dialog__body')
                ->waitForText('THIS LEAD WAS DECLINED AND CAN NO LONGER BE UPDATED.');

            $browser->screenshot('lead_escalation_final_discontinued_success');
        });
    }

    /** @test */
    public function lead_escalation_final_won_success(){
        $this->browse(function (Browser $browser) {
            //dashboard page
            $browser->visit(new Won)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //wait the modal confirmation
            $browser->waitFor('.el-dialog__body')
                ->waitForText('THE ESCALATION HAS ALREADY BEEN COMPLETED.');

            $browser->screenshot('lead_escalation_final_won_success');
        });
    }

    /** @test */
    public function lead_escalation_final_lost_success(){
        $this->browse(function (Browser $browser) {
            //dashboard page
            $browser->visit(new Lost)
                ->maximize();

            //click action dropdown to update lead
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->mouseover('.el-table__row')
                ->click('.drop-down-action-organization')
                ->waitFor('.action-popper')
                ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

            //wait the modal confirmation
            $browser->waitFor('.el-dialog__body')
                ->waitForText('THE ESCALATION HAS ALREADY BEEN COMPLETED.');

            $browser->screenshot('lead_escalation_final_lost_success');
        });
    }
}
