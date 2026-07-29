<?php

namespace App\Providers;

use App\Support\CareServiceType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $ignoreCaresInListPending=[
        CareServiceType::YOUNG_BMI,
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

        $this->app->singleton('ignoreCaresInListPending',$this->ignoreCaresInListPending);
    }
}
