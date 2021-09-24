<?php

namespace Tests\Browser\Pages;

use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;

class Won extends Page
{
    use WithFaker;
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/dashboard';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        //dashboard page
        $browser->visit($this->url())
            ->assertPathIs($this->url())
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

        //click accept lead
        $browser->click('@accepted-lead');

        //wait the modal confirmation
        $browser->waitFor('.escalation-modal')
            ->waitForText('Confirmation');

        //click done in confirmation from accepted
        $browser->click('@confirmation-done');

        //------------------cec-work-in-progress---------------

        //click action dropdown to update lead
        $browser->waitFor('.sc-table')
            ->waitFor('.el-table__body-wrapper')
            ->waitFor('.el-table__body')
            ->mouseover('.el-table__row')
            ->click('.drop-down-action-organization')
            ->waitFor('.action-popper')
            ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

        //click responses - i have contacted the  enquirer
        $browser->waitFor('@cecForm')
            ->elements('.cec-responses > .el-radio__input > .el-radio__inner')[1]->click();

        //click reason - this lead is currently work in progress
        $browser->waitFor('@cecForm')
            ->elements('.cec-reasons > .el-radio__input > .el-radio__inner')[0]->click();

        //click save
        $browser->click('@cec-submit');

        //wait the modal confirmation
        $browser->waitFor('.escalation-modal')
            ->waitForText('Confirmation');

        //click done in confirmation from accepted
        $browser->click('@confirmation-done');

        //click action dropdown to update lead
        $browser->waitFor('.sc-table')
            ->waitFor('.el-table__body-wrapper')
            ->waitFor('.el-table__body')
            ->mouseover('.el-table__row')
            ->click('.drop-down-action-organization')
            ->waitFor('.action-popper')
            ->elements('.action-popper > .el-dropdown-menu__item')[0]->click();

        //this lead is currently work in progress
        $browser->waitFor('@inProgressForm')
            ->elements('.in-progress-responses > .el-radio__input > .el-radio__inner')[3]->click();

        //gutter_edge_meters
        $browser->type('@gutter_edge_meters', 32);

        //valley_meters
        $browser->type('@valley_meters', 42);

        //date installation
        $browser->type('.el-date-editor > .el-input__inner', date('Y-m-d'));

        //click gutter to hide calendar
        $browser->click('@gutter_edge_meters');

        //update in progress
        $browser->click('@in-progress-submit');

        //wait the modal confirmation
        $browser->waitFor('.escalation-modal')
            ->waitForText('Confirmation');

        //click done in confirmation from accepted
        $browser->click('@confirmation-done');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            //'@element' => '#selector',
        ];
    }
}
