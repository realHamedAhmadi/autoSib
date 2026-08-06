<?php

namespace Database\Seeders;

use App\Models\Care;
use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MiddleAgedCareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $title='ارزيابي فعاليت بدني میانسالان';
        $code=6786;
        $this->createCare($code,$title,CareServiceType::MIDDLE_PHYSICAL_ACTIVITY->value);

        $title='ارزيابي سلامت روان میانسالان';
        $code=6784;
        $this->createCare($code,$title,CareServiceType::MIDDLE_MENTAL_HEALTH->value);

        $title='غربالگری مصرف مواد میانسالان';
        $code=8008;
        $this->createCare($code,$title,CareServiceType::MIDDLE_DRUG_USE->value);

        $title='ارزيابي از نظر سلامت اجتماعي میانسالان';
        $code=8004;
        $this->createCare($code,$title,CareServiceType::MIDDLE_VULNERABLE_FAMILY->value);


        $title='ارزيابي نمايه توده بدني میانسالان';
        $code=7982;
        $this->createCare($code,$title,CareServiceType::MIDDLE_BMI->value);

        $title='شناسایی افراد مشکوک به آسم میانسالان';
        $code=24339;
        $this->createCare($code,$title,CareServiceType::MIDDLE_ASTHMA->value);

    }

    protected function careExists($code,$title)
    {
        return Care::type(CareType::MIDDLE_AGED)
            ->where('code',$code)->where('title',$title)->exists();
    }

    protected function createCare($code,$title,$service)
    {
        if (!$this->careExists($code,$title)){
            Care::create(array_merge(
                compact('code','title','service')
                ,['type'=>CareType::MIDDLE_AGED->name]
            ));
        }
    }
}
