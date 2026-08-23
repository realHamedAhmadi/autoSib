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
    protected int $id=41;
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
        Care::type(CareType::THE_ELDERLY)->delete();
    }

    protected function createData()
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
                ,[
                    'id'=>$this->id++,
                    'type'=>CareType::THE_ELDERLY->name
                ]
            ));
        }
    }
};
