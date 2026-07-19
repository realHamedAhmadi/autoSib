<?php

namespace Tests\Browser;

use App\Models\FamilyEnvHealth;
use App\Models\FamilyEnvHealthForm;
use App\Models\User;
use function Brick\Math\toInt;
use function Carbon\int;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use function Termwind\ValueObjects\pr;
use Tests\Browser\Pages\GroupList;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class FamilyEnvHealthFormTest extends DuskTestCase
{


    protected $numberOfUsers = 230; // count set family env health form

    protected $countPerPage = 200;

    protected $exceptCols = [
        'id', 'user_id', 'updated_at', 'created_at'
    ];

    protected $guardianCode = 63;

    protected $onlyHyperTensionAndDiabeticUsers=false;

    /**
     * A Dusk test example.
     */
    public function testFamilyEnvHealth(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login())
                ->loginToSib()
                ->visit(new GroupList())
                ->moreFilter();
            //$tag = $browser->element('#app-form')
            /*->findElements(WebDriverBy::tagName('h1'))[0]*/
            // ->getText();


            echo 'log: 1';
            $browser->type('Id_FamilyRelation', $this->guardianCode);

            $users = [];
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
                        $user = $this->updateAndGetUser($row, $code, $unit);
                        if (!$user)
                            continue;
                        $exists=($user->diabetic()->exists() or $user->hyperTension()->exists());
                        if (!$exists and $this->onlyHyperTensionAndDiabeticUsers)
                            continue;
                        $exists = $user->familyEnvHealthForm()->whereDate('created_at', '>=', $this->getFromDate())->exists();
                        if (!$exists)
                            $users[] = $user;
                    }
                    $nextPage = $browser->element('.nextPage');
                    if (!$nextPage)
                        break;

                    $nextPage->click();
                    $browser->pause(500);
                    echo 'log: 3';
                }
            }

            foreach ($users as $key => $user) {
                if ($this->numberOfUsers < $key + 1)
                    break;
                $browser->visit('/Account/ChooseClient/' . $user->code);
                $browser->visit($this->getEnvHealthLink());
                DB::beginTransaction();
                try {
                    $this->setFormInSibWebsite($browser, $f = $this->setForm($user));
                    $browser->pause('2000');
                    $this->nexForm($browser);
                    $browser->assertSeeIn('.submitNextForm', 'تایید نهایی');
                    $this->nexForm($browser);
                    DB::commit();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    DB::rollBack();
                    //$browser->pause('50000');
                    break;
                }
                //$browser->pause('10000');
                //break;
            }
        });
    }


    protected function setPlumbing($form, $lastForm, $flag)
    {
        if ($flag) {
            $form->col_22173 = 105983;
            $form->col_31511 = 128915;
            $form->col_31498_128850 = 1;
            $form->col_31498_128852 = 1;
        }

        if (!empty($lastForm))
            $form->col_31498_128851 = $lastForm->col_31498_128851;
        else
            $form->col_31498_128851 = rand(0, 1);

    }

    protected function setWaterSources($form, $flag)
    {
        if ($flag)
            $num = 105984;
        else
            $num = 105985;

        $form->col_22173 = $num;

    }

    protected function setToilet($form, $lastForm, $flag)
    {
        $form->col_31518 = 128931;

        if ($flag) {
            $form->col_31496_128844 = 1;
            $form->col_31496_128845 = 1;
            $form->col_31496_128846 = 1;
            $form->col_31496_128847 = 1;
            $form->col_31496_128848 = 1;
            $form->col_31496_128849 = 0;
            return true;
        }

        if (!empty($lastForm)) {
            $form->col_31496_128844 = $lastForm->col_31496_128844;
            $form->col_31496_128845 = $lastForm->col_31496_128845;
            $form->col_31496_128846 = $lastForm->col_31496_128846;
            $form->col_31496_128847 = $lastForm->col_31496_128847;
            $form->col_31496_128848 = $lastForm->col_31496_128848;
            $form->col_31496_128849 = $lastForm->col_31496_128849;
        } elseif (/*rand(0, 1)*/
        true) {
            $form->col_31496_128845 = 1;
        } else
            $form->col_31496_128849 = 1;

        return false;
    }

    protected function setSewage($form, $flag)
    {
        if ($flag)
            $form->col_31517 = 128924;
        else
            $form->col_31517 = 128927;
    }

    protected function setWaste($form, $flag, $unitCode)
    {
        $form->col_31501 = 0;

        if ($flag) {
            if ($unitCode == 101) {
                $form->col_31502 = 128862;
                $form->col_31504 = 128875;
            } else {
                $form->col_31502 = 135022;
                $form->col_31504 = 128871;
            }
            $form->col_31505 = 128888;
        } else {
            $form->col_31502 = 135022;
            $form->col_31504 = 128878;
            $form->col_31505 = 128889;
        }
        $form->col_31503 = 128863;
    }

    protected function setLivestock($form, $flag, $animalWaste)
    {

        if (!$flag) {
            $form->col_31506 = 0;
            return false;
        }

        $form->col_31506 = 1;

        if ($animalWaste) {
            $form->col_31507 = 128894;
        } else {
            $form->col_31507 = 128896;
        }

        return true;
    }


    protected function bathroom($form)
    {
        $form->col_31499 = 1; // has bathroom
        $form->col_31500_128854 = $form->col_31496_128848 ?? 0; // plumbing
        $form->col_31500_128855 = $form->col_31496_128844 ?? 0;  // window
        $form->col_31500_128856 = $form->col_31496_128845 ?? 0;  // door
        $form->col_31500_128857 = $form->col_31496_128846 ?? 0;  // wall

        if ($form->col_31496_128849)
            $form->col_31500_128856 = 1;
    }

    protected function setMisc($form, FamilyEnvHealth $envHealth)
    {
        $form->col_31497 = 0; // common toilet

        if ($envHealth->has_city_gas) {
            $form->col_31508_128903 = 1;
        } else {
            $form->col_31508_128901 = 1;
        }

        $form->col_31512 = 0; // box water

        $form->col_22183 = $envHealth->plumbing ? 0 : 1; // stril kardan

        $form->col_22182 = $envHealth->plumbing ? 0 : 1; // joushandan

        $form->col_22184 = 0; // tasfiyeh


        $form->col_22189 = 0; // makhzan zakhireh

        $form->col_22190 = 106009;  // pompajh Ab

    }


    protected function getItem($form, $col)
    {
        if (!Str::startsWith($col, 'col_'))
            return false;

        $colArr = explode('_', $col);

        //echo $col.'-'.$form->$col;
        if (count($colArr) == 3)
            return [
                'type' => 'check',
                'value' => $form->$col,
                'name' => $colArr[1] . '-' . $colArr[2]
            ];
        elseif (count($colArr) == 2)
            return [
                'type' => 'radio',
                'value' => is_numeric($form->$col),
                'name' => $colArr[1] . '-' . $form->$col,
            ];

        return false;
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

    protected function updateAndGetUser($row, $unitCode, $unitName)
    {
        $userId = $row->findElement(WebDriverBy::className('userId'))->getAttribute('value');
        $tds = $row->findElements(WebDriverBy::tagName('td'));
        $fName = $tds[0]->getText();
        $lName = $tds[1]->getText();
        $nationalId = $tds[2]->getText();
        $user = User::where('national_code', $nationalId)->first();

        if ($user)
            $user->update([
                'code' => $userId,
                'name' => $fName . ' ' . $lName,
                'unit_code' => $unitCode,
                'unit_name' => $unitName,
            ]);

        return $user;
    }

    protected function getEnvHealthLink()
    {
        return '/FamilyCare_/HealthIndex?id_ChildIndex=8524&priority=0&returnUrl=%2FFamilyCare%2FChildIndex%3FchildType%3D121%26tabNumber%3D1';
    }

    protected function getFromDate()
    {
        $month = \jDateTime::date('m');

        $month = intval(($month - 1) / 3) * 3 + 1;
        $month=strlen($month)==2?$month:"0$month";
        $year = \jDateTime::date('Y');

        return \jDateTime::createDatetimeFromFormat('Y-m-d', "$year-$month-01");
    }

    protected function setForm(User $user)
    {
        $form = new FamilyEnvHealthForm();
        $form->user_id = $user->id;

        $lastForm = $user->familyEnvHealthForm()->latest()->first();

        $f = $user->familyEnvHealth;

        $this->setPlumbing($form, $lastForm, $f->plumbing);
        if (!$f->plumbing)
            $this->setWaterSources($form, $f->water_sources);
        $this->setToilet($form, $lastForm, $f->toilet);
        $this->bathroom($form);/////////////
        $this->setSewage($form, $f->sewage);
        $this->setWaste($form, $f->waste, $user->unit_code);
        $this->setLivestock($form, $f->has_livestock, $f->animal_waste);
        $this->setMisc($form, $f);
        $form->save();
        return $form;
    }

    protected function setFormInSibWebsite(Browser $browser, $form)
    {
        //foreach (FamilyEnvHealthForm::getColumns() as $col) {
        foreach (collect(FamilyEnvHealthForm::first())->keys() as $col) {
            $item = $this->getItem($form, $col);
            if ($item) {
                $name = $item['name'];
                //echo "\n $name";
                if ($item['type'] == 'radio' and $item['value']) {
                    $browser->script("$(`[for='$name']`).click();");
                    $browser->pause('1000');
                } elseif ($item['type'] == 'check') {
                    $checked = $item['value'] ? 'true' : 'false';
                    $browser->script(
                        "
                        if($(`#$name`).is(`:checked`)!==$checked){
                           $(`[for='$name']`).click();
                        }
                        "
                    );
                    //$browser->script("$(`#$name`).prop(`checked`,$checked);");
                }
            }
        }
    }

    protected function nexForm(Browser $browser)
    {
        $browser
            ->click('.submitNextForm')
            ->waitUntil('!$.active', 30)
            ->pause(500);
    }
}

