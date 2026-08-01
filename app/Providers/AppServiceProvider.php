<?php

namespace App\Providers;

use App\Support\CareServiceType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $ignoreCaresInListPending=[
        //young
        CareServiceType::YOUNG_BMI,
        CareServiceType::YOUNG_HYPER_TENSION_RISK,
    ];


    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->app->singleton('ignoreCaresInListPending',function (){
            return $this->ignoreCaresInListPending;
        });
    }
}
