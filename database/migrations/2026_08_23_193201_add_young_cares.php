<?php

use App\Models\Care;
use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected int $id=11;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       DB::beginTransaction();
        try {
            $this->createData();
            DB::commit();
        }catch (Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Care::type(CareType::YOUNG_PEOPLE)->delete();
    }

    protected function createData()
    {
        $title='ارزيابي فعاليت بدني جوانان';
        $code=6786;
        $this->createCare($code,$title,CareServiceType::YOUNG_PHYSICAL_ACTIVITY->value);

        $title='ارزيابي سلامت روان جوانان';
        $code=6931;
        $this->createCare($code,$title,CareServiceType::YOUNG_MENTAL_HEALTH->value);

        $title='غربالگری مصرف مواد جوانان';
        $code=7519;
        $this->createCare($code,$title,CareServiceType::YOUNG_DRUG_USE->value);

        $title='ارزيابي از نظر سلامت اجتماعي جوانان';
        $code=7517;
        $this->createCare($code,$title,CareServiceType::YOUNG_VULNERABLE_FAMILY->value);


        $title='ارزيابي نمايه توده بدني جوانان';
        $code=6664;
        $this->createCare($code,$title,CareServiceType::YOUNG_BMI->value);


        $title='ارزيابي از نظر خطر ابتلا به فشار خون بالا جوانان';
        $code=6665;
        $this->createCare($code,$title,CareServiceType::YOUNG_HYPER_TENSION_RISK->value);


        $title='مراقبت از نظر وضعيت دهان و دندان جوانان';
        $code=6668;
        $this->createCare($code,$title,CareServiceType::YOUNG_DENTAL_HEALTH->value);

        $title='شناسایی افراد مشکوک به آسم جوانان';
        $code=24338;
        $this->createCare($code,$title,CareServiceType::YOUNG_ASTHMA->value);
    }

    protected function careExists($code)
    {
        return Care::type(CareType::YOUNG_PEOPLE)
            ->where('code',$code)->exists();
    }

    protected function createCare($code,$title,$service)
    {
        if (!$this->careExists($code)){
            Care::create(array_merge(
                compact('code','title','service')
                ,[
                    'id'=>$this->id++,
                    'type'=>CareType::YOUNG_PEOPLE->name
                ]
            ));
        }
    }
};
