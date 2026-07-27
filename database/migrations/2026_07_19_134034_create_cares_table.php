<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\CareType;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cares', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('code');
            $table->enum('type',[
                CareType::DIABETIC->name,
                CareType::HYPER_TENSION->name,
                CareType::YOUNG_PEOPLE->name,
                CareType::MIDDLE_AGED->name,
                CareType::THE_ELDERLY->name,
                CareType::RISK_ASSESSMENT->name,
            ]);
            $table->string('title',100);
            $table->string('service',100);
            $table->timestamps();
        });
        try {
            DB::table('cares')
                ->insert([
                    [
                        'title'=>'مراقبت ماهانه دیابت',
                        'code'=>8326,
                        'type'=>CareType::DIABETIC->name,
                        'service'=>\App\Support\CareServiceType::DIABETIC->value
                    ],[
                        'title'=>'مراقبت ماهانه فشارخون',
                        'code'=>7971,
                        'type'=>CareType::HYPER_TENSION->name,
                        'service'=>\App\Support\CareServiceType::HYPER_TENSION->value
                    ],
                ]);
        }catch (Exception $e){
            $this->down();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cares');
    }
};
