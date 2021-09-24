<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class AdminLeadToInProgress extends Page
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
        //dashboard
        $browser->visit($this->url())
            ->assertPathIs($this->url())
            ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
            ->maximize();

                //click td tr.
                $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

        //click edit lead
        $browser->waitFor('.el-drawer__body')
        ->waitFor('.el-tabs__nav-scroll')
        ->waitFor('.el-tabs__nav')
        ->click('@edit-lead');

        //check if lead for is in the browser
        $browser->waitFor('.lead-form')
            ->waitForText('Edit Lead Information');

        //select level
        $browser->click('.escalation_level .el-input > .el-input__inner')
            ->waitFor('.escalation_level_popper')
            ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[3]->click();

        //next button
        $browser->click('@leadForm-next');

        //next botton
        $browser->click('@leadForm-next');

        //next botton
        $browser->click('@leadForm-next');

        //create botton
        $browser->click('@leadForm-update');
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
