<?php

namespace Database\Seeders;

use App\Models\Care;
use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ElderlyCareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $title='ارزيابي فعاليت بدني سالمندان';
        $code=6786;
        $this->createCare($code,$title,CareServiceType::ELDERLY_PHYSICAL_ACTIVITY->value);

        $title='غربالگری افسردگی سالمندان';
        $code=6570;
        $this->createCare($code,$title,CareServiceType::ELDERLY_DEPRESSION->value);

        $title='مراقبت از عدم تعادل سالمندان';
        $code=6560;
        $this->createCare($code,$title,CareServiceType::ELDERLY_IMBALANCE->value);

        $title='غربالگری تغذیه در سالمندان';
        $code=6423;
        $this->createCare($code,$title,CareServiceType::ELDERLY_BMI->value);

        $title='شناسایی افراد مشکوک به آسم در سالمندان';
        $code=24339;
        $this->createCare($code,$title,CareServiceType::ELDERLY_ASTHMA->value);

    }

    protected function careExists($code)
    {
        return Care::type(CareType::THE_ELDERLY)
            ->where('code',$code)->exists();
    }

    protected function createCare($code,$title,$service)
    {
        if (!$this->careExists($code)){
            Care::create(array_merge(
                compact('code','title','service')
                ,['type'=>CareType::THE_ELDERLY->name]
            ));
        }
    }
}
