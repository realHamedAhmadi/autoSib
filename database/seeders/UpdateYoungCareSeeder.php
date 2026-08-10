<?php

namespace Database\Seeders;

use App\Models\Care;
use App\Support\CareType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateYoungCareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // young asthma
        Care::type(CareType::YOUNG_PEOPLE)->where('code',24339)->update([
            'code'=>24338
        ]);
    }
}
