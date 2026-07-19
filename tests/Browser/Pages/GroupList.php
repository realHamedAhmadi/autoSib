<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class GroupList extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/Group_/GroupList';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
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

    public function moreFilter(Browser $browser):void
    {
        $browser->click('.btn-sm.more')
        ->pause('3000');
    }
}
