<?php

namespace App\Exports\Sheets;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InsuranceStatPerUnitSheet implements WithTitle,FromCollection,
    WithColumnFormatting,WithColumnWidths {
    public function __construct(protected Collection $stats,protected $types,private $unitCode){}

    public function collection()
    {
        $collect=collect();
        foreach ($this->types as $key=>$type){
            $array=[];
            $c=$this->stats->where('type_code',$key)
                ->where('unit_code',$this->unitCode);
            $array[]=$type;
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
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'=>15,
        ];
    }

}
