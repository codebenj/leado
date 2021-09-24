<?php

namespace Tests\Browser\Pages;

class Login extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/login';
    }

    /**
     * Submit the form with the given credentials.
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @param  string $email
     * @param  string $password
     * @return void
     */
    public function submit($browser, $email, $password)
    {
        $browser->type('@login-email', $email)
                ->type('@login-password', $password)
                ->click('@login-button')
                ->pause(500)
                ->visit('/dashboard');
    }
}
