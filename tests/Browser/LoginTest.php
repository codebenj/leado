<?php

namespace Tests\Browser;

use App\User;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function setUp(): void
    {
        parent::setup();

        static::closeAll();
    }

    /** @test */
    public function login_with_valid_credentials()
    {
        $user = factory(User::class)->create();

        $this->browse(function ($browser) use ($user) {
            $browser->visit(new Login)
                ->submit($user->email, 'password')
                ->maximize();

            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            $browser->pause(500);

            $browser->screenshot('login_with_valid_credentials');
        });
    }

    /** @test */
    public function login_with_invalid_credentials()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit('test@test.app', 'password')
                ->assertPathIs('/login');

            $browser->pause(500);

            $browser->screenshot('login_with_invalid_credentials');
        });
    }
}
