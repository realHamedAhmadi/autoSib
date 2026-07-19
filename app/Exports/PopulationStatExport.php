<?php

namespace App\Exports;

use App\Exports\Sheets\PopulationStatPerUnitCodeSheet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PopulationStatExport implements WithMultipleSheets
{
    use Exportable;
    protected array $unitCodes;
    /**
     * Create a new class instance.
     */
    public function __construct(protected Collection $stats, protected array $ageGroup)
    {
        $this->unitCodes=$stats->groupBy('unit_code')->pluck('0.unit_code')->toArray();
    }

    public function sheets(): array
    {
        $sheets=[];
        foreach ($this->unitCodes as $code){
            $sheets[]=new PopulationStatPerUnitCodeSheet($this->stats,$this->ageGroup,$code);
        }

        return $sheets;
    }
}
