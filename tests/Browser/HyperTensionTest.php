<?php

namespace Tests\Browser;

use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\GroupList;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use App\Models\HyperTension as HyperTensionModel;
use function PHPUnit\Framework\isFalse;

class HyperTensionTest extends DuskTestCase
{

    protected $countPerPage = 200;

    protected $hyperTension = 1077;

    protected $numberOfUsers = 20;

    protected $onlyActiveUsers=false;

    /**
     * A Dusk test example.
     */
    public function testHyperTensionMonthly(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login())
                ->loginToSib()
                ->visit(new GroupList())
                ->moreFilter();

            $browser->select('Id_Sick', $this->hyperTension);

            $this->getUsersInGroupList($browser,function ($user){
                if (in_array($user->national_code,$this->activeUsersNationalCode)){
                    return (!$this->onlyActiveUsers or $user->hyperTension()->whereDate('created_at', '>=', $this->getFromDate())->exists());
                }
                return ($this->onlyActiveUsers or $user->hyperTension()->whereDate('created_at', '>=', $this->getFromDate())->exists());
            });
            shuffle($this->users);
            foreach ($this->users as $key => $user) {
                if ($this->numberOfUsers < $key + 1)
                    break;
                $browser->visit('/Account/ChooseClient/' . $user->code);
                $h = $this->hyperTension($browser, $user);
                if ($h == 'continue')
                    continue;
                elseif ($h == 'break')
                    break;
            }
        });
    }

    public function hyperTension(Browser $browser, $user)
    {
        $browser->visit($this->getHyperTensionLink());
        DB::beginTransaction();
        try {
            echo 'Log 4';
            if (!$this->isValidForm($browser)) {
                return 'continue';
            }
            echo 'Log 5';
            $this->setFormInSibWebsite($browser, $f = $this->setForm($user));
            $browser->pause('3000');
            $this->nexForm($browser);
            $browser->assertSeeIn('.submitNextForm', 'تایید نهایی');
            $this->nexForm($browser, true);
            DB::commit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            DB::rollBack();
            //$browser->pause('50000');
            return 'break';
        }
        $exists = $user->diabetic()->whereDate('created_at', '>=', $this->getFromDate())->exists();
        $exists = (!$exists and $user->diabetic()->whereDate('created_at', '<', $this->getFromDate())->exists());
        if ($exists)
            (new DiabetesTest($this->name()))->diabetes($browser, $user);
        return true;
    }

    protected function updateAndGetUser($row, $unitCode, $unitName)
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

    protected function getFromDate()
    {
        $month = \jDateTime::date('m');

        $month = intval($month);
        $month = strlen($month) == 2 ? $month : "0$month";
        $year = \jDateTime::date('Y');

        return \jDateTime::createDatetimeFromFormat('Y-m-d', "$year-$month-01");
    }

    protected function getHyperTensionLink()
    {
        return 'https://sib.umsu.ac.ir/FamilyCare_/HealthIndex?id_ChildIndex=7971&priority=0&returnUrl=%2FFamilyCare%2FChildIndex%3FchildType%3D121%26tabNumber%3D2';
    }


    protected function nexForm(Browser $browser, $next = false)
    {
        $removeTracks = $browser->elements('.removeTrack');
        foreach ($removeTracks as $element) {
            $element->click();
        }
        $browser
            ->click('.submitNextForm')
            ->waitUntil('!$.active', 30)
            ->pause(1000);
        $modal = $browser->element('#alertReferralModal');
        if ($modal and $next) {
            echo 'Log 6';
            $modal->findElement(WebDriverBy::className('btn-warning'))->click();
            $browser->pause(100);
            echo 'Log 7';
            $browser->select('#dropNotReferralCause', 199);
            $browser->pause(200);
            $browser->click('.submitFormAlertReferral')
                ->waitUntil('!$.active', 30)
                ->pause(500);
        }
    }

    protected function setFormInSibWebsite(Browser $browser, $form)
    {
        $this->setCheckbox($browser, '31565-128980');

        $this->setInputByDataId($browser, 10021, $form->sys);
        $this->setInputByDataId($browser, 10112, $form->dias);

        $this->setInputByDataId($browser, 10362, $form->sys);
        $this->setInputByDataId($browser, 10411, $form->dias);

        $this->setInputByDataId($browser, 10094, $form->sys - $this->arrayRandom([0, 5]));
        $this->setInputByDataId($browser, 10927, $form->dias - $this->arrayRandom([0, 5]));


        $browser->script("$(`[for='31554-1']`).click();");
        $browser->script("$(`[for='31555-1']`).click();");
        $browser->script("$(`[for='31556-0']`).click();");

        $browser->script("$(`[for='31557-0']`).click();");
        $browser->script("$(`[for='31552-0']`).click();");

        $browser->script("$(`[for='11646-0']`).click();");
        $browser->script("$(`[for='12447-0']`).click();");

        $browser->script("$(`[for='31560-1']`).click();");
        $browser->script("$(`[for='31561-1']`).click();");

        $browser->script("$(`[for='31545-1']`).click();"); //robust

    }

    protected function setForm(User $user)
    {
        $form = new HyperTensionModel();
        $form->user_id = $user->id;
        $lastForm = $user->hyperTension()->latest()->first();
        $sysValues = [110, 115, 120, 125, 130, 135, 140];
        $diasValues = [70, 75, 80, 85];
        $diffValues = [0, 5, -5];
        if ($lastForm and $lastForm->sys) {
            $diffValue = $this->arrayRandom($diffValues);
            $value = $lastForm->sys + $diffValue;
            $value = max(110, $value);
            $value = min(140, $value);
            $form->sys = $value;
            $value = $lastForm->dias + $diffValue;
            $value = max(70, $value);
            $value = min(90, $value);
            $form->dias = $value;
        } else {
            $value = $this->arrayRandom($sysValues);
            if ($value == 140) {
                $value = $this->arrayRandom($sysValues);
            }
            $form->sys = $value;
            $form->dias = $this->arrayRandom($diasValues);
        }
        $form->save();
        return $form;
    }

    protected function setCheckbox(Browser $browser, $id)
    {
        $browser->script(
            "
                        if($(`#$id`).is(`:checked`)!==true){
                           $(`[for='$id']`).click();
                        }
                        "
        );
    }

    protected function isValidForm(Browser $browser)
    {
        //return $browser->inputValue('[@"data-id"=10001]') and $browser->inputValue('[@"data-id"=10002]');
        return $browser->script("
        return !($('input[data-id=10001]').val() && $('input[data-id=10002]').val());
        ");
    }

    protected function setInputByDataId(Browser $browser, $id, $value)
    {
        return $browser->script("
        $('input[data-id=$id]').val($value);
        ");
    }
}
