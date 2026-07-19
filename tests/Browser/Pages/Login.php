<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class Login extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/home/dashboard';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        //$browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@element' => '#selector',
        ];
    }

    public function loginToSib(Browser $browser, $username = null, $password = null)
    {
        if ($browser->element('#saveBtn')){

            $browser->type('UserName', $username ?? '4930000122')
                ->type('Password', $password ?? 'Ha@123456!');
            while (true) {
                if (strlen($browser->inputValue('Captcha'))==4)
                    break;
                $browser->pause(1000);
            }
            $browser->click('#saveBtn')
                ->assertPathBeginsWith('/Home/SelectRole')
                ->pause(5000)
                //->radio('RoleId', '1370052629')
                ->click('.btn-dapa')
                ->assertPathBeginsWith('/home/dashboard');
        }
    }
}
