<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Declined extends Page
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

        //click declined
        $browser->click('@declined-lead');

        //click first reasons
        $browser->click('.decline_reason .el-input > .el-input__inner')
            ->waitFor('.decline_reason_popper')
            ->elements('.decline_reason_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[0]->click();

        //click decline save
        $browser->click('@declined-lead-save');

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
