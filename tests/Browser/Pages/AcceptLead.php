<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class AcceptLead extends Page
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
        $browser->assertPathIs($this->url())
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

        //click accept
        $browser->click('@accepted-lead');

        //wait the modal confirmation
        $browser->waitFor('.escalation-modal')
            ->waitForText('Confirmation');

        //click done to close modal
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
