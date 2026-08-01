<?php

namespace Database\Seeders;

use App\Models\Care;
use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YoungCareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $title='ارزيابي فعاليت بدني';
        $code=6786;
        $this->createCare($code,$title,CareServiceType::YOUNG_PHYSICAL_ACTIVITY->value);

        $title='ارزيابي سلامت روان جوانان';
        $code=6931;
        $this->createCare($code,$title,CareServiceType::YOUNG_MENTAL_HEALTH->value);

        $title='غربالگری مصرف مواد';
        $code=7519;
        $this->createCare($code,$title,CareServiceType::YOUNG_DRUG_USE->value);

        $title='ارزيابي از نظر سلامت اجتماعي';
        $code=7517;
        $this->createCare($code,$title,CareServiceType::YOUNG_VULNERABLE_FAMILY->value);


        $title='ارزيابي نمايه توده بدني';
        $code=6664;
        $this->createCare($code,$title,CareServiceType::YOUNG_BMI->value);


        $title='ارزيابي از نظر خطر ابتلا به فشار خون بالا';
        $code=6665;
        $this->createCare($code,$title,CareServiceType::YOUNG_HYPER_TENSION_RISK->value);


        $title='مراقبت از نظر وضعيت دهان و دندان';
        $code=6668;
        $this->createCare($code,$title,CareServiceType::YOUNG_DENTAL_HEALTH->value);



    }

    protected function careExists($code,$title)
    {
        return Care::type(CareType::YOUNG_PEOPLE)
            ->where('code',$code)->where('title',$title)->exists();
    }

    protected function createCare($code,$title,$service)
    {
        if (!$this->careExists($code,$title)){
            Care::create(array_merge(
                compact('code','title','service')
                ,['type'=>CareType::YOUNG_PEOPLE->name]
            ));
        }
    }
}
