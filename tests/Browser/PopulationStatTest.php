<?php

namespace Tests\Browser;

use App\Exports\PopulationStatExport;
use App\Models\PopulationStat;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Browser\Pages\GroupList;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class PopulationStatTest extends DuskTestCase
{
    protected $ages = [];
    protected $maxAge = 8;
    protected $beginYear = 1403;
    protected $statTitle = '1403';
    protected $centerCode = '1370011333';
    protected $centerName = 'قنات';


    protected $types = [
        'men' => '1',
        'women' => '2',
        'married_women' => '2'
    ];

    protected $marriedWomenRange = [
        '1390/01/01', '1353/12/29'
    ];

    protected $ageCategories = [
        '1403/12/01-1403/12/29' => 'زیر یکماه',
        '1403/01/01-1403/11/30' => 'یکماه تا یکسال',
        '1399/01/01-1402/12/29' => '1-4 سال',
        '1397/01/01-1398/12/29' => '5-6 سال',
        '1394/01/01-1396/12/30' => '7-9 سال',
        '1390/01/01-1393/12/29' => '10-14 سال',
        '1386/01/01-1389/12/29' => '15-17 سال',
        '1384/01/01-1385/12/29' => '18-19 سال',
    ];

    /**
     * A Dusk test example.
     */
    public function testGetStat()
    {
        $this->getStat();
    }

    public function testGetByAgeCategory()
    {
        $this->getStat(true);
    }

    public function ageGroup($byAgeCategory = true)
    {
        if ($byAgeCategory) {
            return $this->ageCategory();
        }

        return $this->yearly();
    }

    protected function ageCategory()
    {
        if (count($this->ageCategories) > 10)
            return $this->ageCategories;
        for ($i = 20; $i <= 80; $i += 5) {
            $a = $i . '-' . $i + 4;
            $a .= ' سال';
            $to = $this->beginYear - $i;
            $from = $to - 4;
            $day = 30;
            if (!\jDateTime::isValidateJalaliDate($to, 12, $day))
                $day = 29;
            $this->ageCategories["$from/01/01-$to/12/$day"] = $a;
        }
        $to = $this->beginYear - $i;
        if (!\jDateTime::isValidateJalaliDate($to, 12, $day))
            $day = 29;
        $from = $to - 26;
        $this->ageCategories["$from/01/01-$to/12/$day"] = '85 و بالاتر';
        return $this->ageCategories;
    }

    protected function yearly()
    {
        if ($this->ages)
            return $this->ages;

        for ($i = 1; $i <= $this->maxAge; $i++) {
            $a = $i;
            $a .= ' ساله';
            $year = $this->beginYear - $i;
            $day = 30;
            if ($i==$this->maxAge){
                $year2=$year-1;
            }else{
                $year2=$year;
            }
            if (!\jDateTime::isValidateJalaliDate($year, 12, $day))
                $day = 29;

            $this->ages["$year2/01/01-$year/12/$day"] = $a;
        }
        return $this->ages;
    }

    protected function getTypes($byAgeCategory = false)
    {
        if (!$byAgeCategory)
            unset($this->types['married_women']);
        return $this->types;
    }

    protected function getStat($byAgeCategory = false)
    {
        $stats = new Collection();
        PopulationStat::truncate();

        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) use ($byAgeCategory, &$stats) {
            $browser
                ->visit(new Login())
                ->loginToSib()
                ->visit(new GroupList())
                ->moreFilter();

            $this->centerCode = $browser->element('#NetworkListMenu .caret')->getAttribute('data-id');
            $this->centerName = $browser->element('#NetworkListMenu')->getText();

            $browser->element('#Id_BlockNumber.dapa-multi-select #chips')->click();
            $browser->pause(200);
            $units = $browser->elements('#Id_BlockNumber.dapa-multi-select #data-container tr');
            $unitsArr = [];
            foreach ($units as $unit) {
                $unitCode = $unit->getAttribute('id');
                if ($unitCode == 1)
                    continue;
                $unitsArr[$unitCode] = $unit->getText();
            }
            foreach ($unitsArr as $code => $unit) {
                $browser->type('Id_BlockNumber', $code);
                $browser->type('Id_MarriageTyp', '');
                foreach ($this->getTypes($byAgeCategory) as $key => $type) {
                    if ($key == 'married_women')
                        $browser->type('Id_MarriageTyp', '2');

                    $browser->select('Gender', $type);
                    $marriedWomenFlag = false;
                    foreach ($this->ageGroup($byAgeCategory) as $keyAge => $category) {
                        [$fromDate, $toDate] = explode('-', $keyAge);
                        if ($key == 'married_women') {
                            if (in_array($fromDate, $this->marriedWomenRange))
                                $marriedWomenFlag = true;

                            if (!$marriedWomenFlag)
                                continue;
                        }

                        $browser->type('BirthDateFrom', $fromDate)
                            ->type('BirthDateTo', $toDate);
                        ///search
                        $this->search($browser);
                        //$browser->pause(1000);
                        /// save to database
                        $stat = new PopulationStat();
                        $stat->stat = $this->statTitle;
                        $stat->ageType = $byAgeCategory ? 'ageCategory' : 'yearly';
                        $stat->center_code = $this->centerCode;
                        $stat->center_name = $this->centerName;
                        $stat->unit_code = $code;
                        $stat->unit_name = $unit;
                        $stat->age_category = $category;
                        $stat->from_birthdate = $fromDate;
                        $stat->to_birthdate = $toDate;
                        $stat->type = $key;
                        $stat->number = $browser->element('#span_numberOfRecords')->getText();
                        $stat->save();
                        $stats->add($stat);
                        if ($key == 'married_women' and in_array($toDate, $this->marriedWomenRange))
                            break;
                    }
                }
            }
        });

        $filename = 'population-stat';
        if ($byAgeCategory)
            $filename .= '-ageCat';
        $filename .= '.xlsx';
        File::delete(storage_path('app/' . $filename));
        Excel::store(new PopulationStatExport($stats, $this->ageGroup($byAgeCategory)), $filename);
    }
}
