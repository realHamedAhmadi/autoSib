<?php

namespace Tests\Browser;

use App\Exports\InsuranceStatExport;
use App\Models\InsuranceStat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Browser\Pages\GroupList;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class InsuranceStatTest extends DuskTestCase
{

    protected $year = 1403;
    protected $statTitle = '1403';
    protected $gender = [
        'men' => '1',
        'women' => '2',
    ];

    protected $types = [
        77=>'سلامت  روستایی',
        78=>'سلامت  غیر روستایی',
        79=> 'تامین اجتماعی',
        80=>'نیرو های مسلح',
        81=> 'کمیته امداد',
        93=>'سلامت ایرانیان',
        95=>'خدمات درمانی',
        82=>'سایر',
        83=>'ندارد',
    ];

    /**
     * A Dusk test example.
     */
    public function testInsuranceStat(): void
    {
        $stats = new Collection();
        InsuranceStat::truncate();
        $this->browse(function (Browser $browser) use (&$stats) {
            $browser->visit(new Login())
                ->loginToSib()
                ->visit(new GroupList())
                ->moreFilter();
            $day = 30;
            if (!\jDateTime::isValidateJalaliDate($this->year, 12, $day))
                $day = 29;
            $browser->type('BirthDateTo', "{$this->year}/12/$day");
            $centerCode = $browser->element('#NetworkListMenu .caret')->getAttribute('data-id');
            $centerName = $browser->element('#NetworkListMenu')->getText();
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
                foreach ($this->gender as $gKey=>$gender) {
                    $browser->select('Gender', $gender);
                    foreach ($this->types as $typeCode=>$type) {
                        $browser->type('Id_InsuranceType',$typeCode);
                        $this->search($browser);
                        $stat = new InsuranceStat();
                        $stat->stat = $this->statTitle;
                        $stat->center_code = $centerCode;
                        $stat->center_name = $centerName;
                        $stat->unit_code = $code;
                        $stat->unit_name = $unit;
                        $stat->type = $type;
                        $stat->type_code=$typeCode;
                        $stat->gender = $gKey;
                        $stat->number = $browser->element('#span_numberOfRecords')->getText();
                        $stat->save();
                        $stats->add($stat);
                    }
                }
            }
        });

        $filename = 'insurance-stat.xlsx';
        File::delete(storage_path('app/' . $filename));
        Excel::store(new InsuranceStatExport($stats,$this->types), $filename);
    }

    protected function search(Browser $browser)
    {
        $browser
            ->click('#btnSearch')
            ->waitUntil('!$.active', 30)
            ->pause(500);
    }
}
