<?php

namespace Tests\Browser;

use App\Models\Diabetic;
use App\Models\HyperTension as HyperTensionModel;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\GroupList;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class DiabetesTest extends DuskTestCase
{

    protected $countPerPage = 200;

    protected $diabetes = 1061;

    protected $numberOfUsers = 5;

    protected $onlyActiveUsers=true;

    /**
     * A Dusk test example.
     */
    public function testDiabeticMonthly(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login())
                ->loginToSib()
                ->visit(new GroupList())
                ->moreFilter();

            $browser->select('Id_Sick', $this->diabetes);
            $this->getUsersInGroupList($browser,function ($user){
                if (in_array($user->national_code,$this->activeUsersNationalCode)){
                    return (!$this->onlyActiveUsers or $user->diabetic()->whereDate('created_at', '>=', $this->getFromDate())->exists());
                }
                return ($this->onlyActiveUsers or $user->diabetic()->whereDate('created_at', '>=', $this->getFromDate())->exists());
            });
            shuffle($this->users);
            foreach ($this->users as $key => $user) {
                if ($this->numberOfUsers < $key + 1)
                    break;
                $browser->visit('/Account/ChooseClient/' . $user->code);
                $d = $this->diabetes($browser, $user);
                if ($d == 'continue')
                    continue;
                elseif ($d == 'break')
                    break;
            }

        });
    }

    public function diabetes(Browser $browser, $user)
    {
        $browser->visit($this->getDiabetesLink());
        DB::beginTransaction();
        try {
            echo 'Log 4';
            if (!$this->isValidForm($browser)) {
                return 'continue';
            }
            echo 'Log 5';
            $this->setFormInSibWebsite($browser, $f = $this->setForm($user));
            $browser->pause('2000');
            $this->nexForm($browser);
            $browser->assertSeeIn('.submitNextForm', 'تایید نهایی');
            $this->nexForm($browser, true);
            DB::commit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            DB::rollBack();
            //$browser->pause('30000');
            return 'break';
        }
        $exists = $user->hyperTension()->whereDate('created_at', '>=', $this->getFromDate())->exists();
        $exists =(!$exists and $user->hyperTension()->whereDate('created_at', '<', $this->getFromDate())->exists());
        if ($exists){
            echo $this->name();
            (new HyperTensionTest($this->name()))->hyperTension($browser,$user);
        }
        return true;
    }

    protected function getFromDate()
    {
        $month = \jDateTime::date('m');

        $month = intval($month);
        $month = strlen($month) == 2 ? $month : "0$month";
        $year = \jDateTime::date('Y');

        return \jDateTime::createDatetimeFromFormat('Y-m-d', "$year-$month-01");
    }

    protected function getDiabetesLink()
    {
        return 'https://sib.umsu.ac.ir/FamilyCare_/HealthIndex?id_ChildIndex=8326&priority=0&returnUrl=%2FFamilyCare%2FChildIndex%3FchildType%3D121%26tabNumber%3D2';
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
        $this->setCheckbox($browser, '29765-116917');

        $this->setInputByDataId($browser, 10106, $form->fbs);

        $hyperTension = $form->user->hyperTension()->latest()->first();
        if ($hyperTension and $hyperTension->sys) {
            $sys = $hyperTension->sys;
            $dias = $hyperTension->dias;
        } elseif ($hyperTension){
            $sys = $this->arrayRandom([120,125,130,135]);
            $dias = $this->arrayRandom([70, 75, 80,85]);
        } else {
            $sys = $this->arrayRandom([100, 110, 115, 120]);
            $dias = $this->arrayRandom([60, 65, 70, 75, 80]);
        }
        $this->setInputByDataId($browser, 10021, $sys);
        $this->setInputByDataId($browser, 10112, $dias);

        if ($hyperTension) {
            $browser->script("$(`[for='29515-1']`).click();");
            $browser->script("$(`[for='29516-1']`).click();");
            $browser->script("$(`[for='29608-1']`).click();");
        } else
            $browser->script("$(`[for='29515-0']`).click();");


        $browser->script("$(`[for='15149-0']`).click();");

        $browser->script("$(`[for='15792-1']`).click();");

        if ($form->type == 1) {
            $browser->script("$(`[for='29677-0']`).click();");
            $browser->script("$(`[for='29679-1']`).click();");
        } else {
            $browser->script("$(`[for='29677-1']`).click();");
            $browser->script("$(`[for='29678-1']`).click();");

            $browser->script("$(`[for='29679-0']`).click();");
        }

        $browser->script("$(`[for='29513-0']`).click();");
        $browser->script("$(`[for='29516-0']`).click();");

        $browser->script("$(`[for='25534-0']`).click();");

    }

    protected function setForm(User $user)
    {
        $form = new Diabetic();
        $form->user_id = $user->id;
        $lastForm = $user->diabetic()->latest()->first();
        if (!@$lastForm->fbs) {
            $form->fbs = rand(101, 125);
        }else
            $form->fbs=$lastForm->fbs;
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
