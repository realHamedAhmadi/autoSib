<?php

use App\Models\Care;
use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected int $id=26;
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
        Care::type(CareType::MIDDLE_AGED)->delete();
    }

    protected function createData()
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

    protected function careExists($code)
    {
        return Care::type(CareType::MIDDLE_AGED)
            ->where('code',$code)->exists();
    }

    protected function createCare($code,$title,$service)
    {
        if (!$this->careExists($code)){
            Care::create(array_merge(
                compact('code','title','service')
                ,[
                    'id'=>$this->id++,
                    'type'=>CareType::MIDDLE_AGED->name
                ]
            ));
        }
    }
};
