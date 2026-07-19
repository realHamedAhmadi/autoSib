<?php

namespace App\Exports\Sheets;

use Illuminate\Database\Eloquent\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PopulationStatPerUnitCodeSheet implements WithTitle,FromCollection,
    WithColumnFormatting,WithColumnWidths {
    public function __construct(protected Collection $stats,protected array $ageGroup,private $unitCode){}

    public function collection()
    {
        $collect=collect();
        foreach ($this->ageGroup as $range=>$group){
            $array=[];
            [$fromDate, $toDate] = explode('-', $range);
            $c=$this->stats->where('from_birthdate',$fromDate)
                ->where('to_birthdate',$toDate)
                ->where('unit_code',$this->unitCode);
            $array[]=$group;
            $array[]=$fromDate;
            $array[]=$toDate;
            foreach ($c as $item){
                $array[]=$item->number;
            }
            $collect->add($array);
        }
        return $collect;
    }

    public function title(): string
    {
        return $this->stats->where('unit_code',$this->unitCode)->first()->unit_name??$this->unitCode;
    }

    public function columnFormats(): array
    {
        return [
            'D'=>NumberFormat::FORMAT_NUMBER,
            'E'=>NumberFormat::FORMAT_NUMBER,
            'F'=>NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'=>12,
            'B'=>10,
            'C'=>10,
        ];
    }

}
