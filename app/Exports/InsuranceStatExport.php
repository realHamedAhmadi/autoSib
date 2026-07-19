<?php

namespace App\Exports;

use App\Exports\Sheets\InsuranceStatPerUnitSheet;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InsuranceStatExport implements WithMultipleSheets
{
    use Exportable;
    protected array $unitCodes;
    /**
     * Create a new class instance.
     */
    public function __construct(protected Collection $stats,protected $types)
    {
        $this->unitCodes=$stats->groupBy('unit_code')->pluck('0.unit_code')->toArray();
    }

    public function sheets(): array
    {
        $sheets=[];
        foreach ($this->unitCodes as $code){
            $sheets[]=new InsuranceStatPerUnitSheet($this->stats,$this->types,$code);
        }

        return $sheets;
    }
}
