<?php

namespace App\Providers;

use App\Data\Sib\User\SibAdminUserInfo;
use App\Models\User;
use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $ignoreCaresInListPending=[
        CareServiceType::DIABETIC,
        CareServiceType::HYPER_TENSION,

        //young
        CareServiceType::YOUNG_BMI,
        CareServiceType::YOUNG_HYPER_TENSION_RISK,

        //middle aged
        CareServiceType::MIDDLE_BMI,

        //the elderly
        CareServiceType::ELDERLY_BMI,
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

        Request::macro('setSibAdminUser', function (SibAdminUserInfo $info): Request {
            $this->attributes->set('sibAdminUserInfo', $info);

            return $this;
        });
        Request::macro('getSibAdminUser', function (): ?SibAdminUserInfo {
            return $this->attributes->get('sibAdminUserInfo');
        });

        Gate::define('auth',function (User $user){
           return  $user?->hasRole() || $user?->isAdmin();
        });

        Gate::define('sibAdminUser',function (User $user){
            return $user?->hasRole();
        });

        Gate::define('admin',function (User $user){
            return $user?->isAdmin();
        });

        Gate::define('owner',function (User $user){
            return $user?->isOwner();
        });

        Gate::define('allowedCare',function (User $user,CareType $type){
            return $user->isAdmin() || $user->canAccessCareType($type);
        });
    }
}
