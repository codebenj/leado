<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CecLead extends Page
{
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
        $browser->visit('/dashboard')
            ->assertPathIs('/dashboard')
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
