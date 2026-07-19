<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Exports\PopulationStatExport;
use App\Models\FamilyEnvHealthForm;
use App\Models\PopulationStat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Browser\PopulationStatTest;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        echo FamilyEnvHealthForm::latest()->first()->col_22182;
    }
}
