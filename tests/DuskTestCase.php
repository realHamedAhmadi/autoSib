<?php

namespace Tests;

use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{

    protected $users=[];

    protected $countPerPage=200;

    protected $activeUsersNationalCode=[
        '4930000122',
        '4939891054',
        '2802749986',
        '2802666185',
        '4939600866',
        '4929395569',
        '4939600068',
        '4939858405',
        '4939855260',
        '4939599272',
        '4929657520',
        '4939841758',
        '4939599027',
        '4939598802',
        '4939828786',
        '4939823490',
        '4939818721',
        '4939598365',
        '4939593126',
        '4939804429',
        '4939796019',
        '4939792749',
        '4939788393',
        '4939597903',
        '4939787771',
        '4939912639',
        '4939600122',
        '4939597563',
        '4939597563',
        '4939599825',
        '4939599450',
        '4939598217',
        '6409641631',
        '2801204668',
        '4939598098',
    ];
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver([
                '--port=9515'
            ]);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) ||
               isset($_ENV['DUSK_START_MAXIMIZED']);
    }


    protected function getUsersInGroupList(Browser  $browser,\Closure $existsItem)
    {
        $browser->element('#Id_BlockNumber.dapa-multi-select #chips')->click();
        $browser->pause(200);
        $units = $browser->elements('#Id_BlockNumber.dapa-multi-select #data-container tr');
        $unitsArr = [];

        foreach ($units as $unit) {
            $unitCode = $unit->getAttribute('id');
            if ($unitCode == 1)
                continue;
            $unitsArr[$unitCode] = $unit->getText();
            //break;//<-----
        }

        echo 'log: 2';
        foreach ($unitsArr as $code => $unit) {
            $browser->type('Id_BlockNumber', $code);
            $this->search($browser);

            while (true) {
                $rows = $browser->elements('#tbodyData > tr');
                foreach ($rows as $row) {
                    $user = $this->firstOrNewUser($row, $code, $unit);
                    if (!$user)
                        continue;

                    if (!$existsItem($user))
                        $this->users[] = $user;
                }
                $nextPage = $browser->element('.nextPage');
                if (!$nextPage)
                    break;

                $nextPage->click();
                $browser->pause(500);
                echo 'log: 3';
            }
        }

        return $this->users;
    }

    protected function search(Browser $browser)
    {
        /*$browser->script(
            'jQuery.find(`#formSearchGroupList`)[0].append(`<input type="hidden" name="CountPerPage" value="200">`);'
        );*/
        $browser->script(
            '$(`input#Id_Stage`).attr("name","CountPerPage").val(' . $this->countPerPage . ');'
        );
        $browser
            ->click('#btnSearch')
            ->waitUntil('!$.active', 30)
            ->pause(500);
        //$browser->pause(50000);
    }

    protected function firstOrNewUser($row, $unitCode, $unitName)
    {
        $userId = $row->findElement(WebDriverBy::className('userId'))->getAttribute('value');
        $tds = $row->findElements(WebDriverBy::tagName('td'));
        $fName = $tds[0]->getText();
        $lName = $tds[1]->getText();
        $nationalId = $tds[2]->getText();
        $user = User::firstOrNew([
            'national_code' => $nationalId,
        ]);

        $user->code = $userId;
        $user->name = $fName . ' ' . $lName;
        $user->unit_code = $unitCode;
        $user->unit_name = $unitName;
        $user->save();
        return $user;
    }

    protected function arrayRandom(array $array)
    {
        return $array[array_rand($array)];
    }

}
