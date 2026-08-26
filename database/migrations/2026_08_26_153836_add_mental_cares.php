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
    protected int $id=56;
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
        Care::type(CareType::MENTAL)->delete();
    }

    protected function createData()
    {
        $title='ارزيابي سلامت روان جوانان';
        $code=6931;
        $this->createCare($code,$title,CareServiceType::YOUNG_MENTAL_HEALTH->value);

        $title='غربالگری مصرف مواد جوانان';
        $code=7519;
        $this->createCare($code,$title,CareServiceType::YOUNG_DRUG_USE->value);

        $title='ارزيابي از نظر سلامت اجتماعي جوانان';
        $code=7517;
        $this->createCare($code,$title,CareServiceType::YOUNG_VULNERABLE_FAMILY->value);

        $title='ارزيابي سلامت روان میانسالان';
        $code=6784;
        $this->createCare($code,$title,CareServiceType::MIDDLE_MENTAL_HEALTH->value);

        $title='غربالگری مصرف مواد میانسالان';
        $code=8008;
        $this->createCare($code,$title,CareServiceType::MIDDLE_DRUG_USE->value);

        $title='ارزيابي از نظر سلامت اجتماعي میانسالان';
        $code=8004;
        $this->createCare($code,$title,CareServiceType::MIDDLE_VULNERABLE_FAMILY->value);

        $title='غربالگری افسردگی سالمندان';
        $code=6570;
        $this->createCare($code,$title,CareServiceType::ELDERLY_DEPRESSION->value);
    }

    protected function careExists($code)
    {
        return Care::type(CareType::MENTAL)
            ->where('code',$code)->exists();
    }

    protected function createCare($code,$title,$service)
    {
        if (!$this->careExists($code)){
            Care::create(array_merge(
                compact('code','title','service')
                ,[
                    'id'=>$this->id++,
                    'type'=>CareType::MENTAL->name
                ]
            ));
        }
    }
};
