<?php

namespace App\Providers;

use App\Data\Sib\User\SibAdminUserInfo;
use App\Support\CareServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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

        Request::macro('setSibAdminUser', function (SibAdminUserInfo $info): Request {
            $this->attributes->set('sibAdminUserInfo', $info);

            return $this;
        });
        Request::macro('getSibAdminUser', function (): ?SibAdminUserInfo {
            return $this->attributes->get('sibAdminUserInfo');
        });

        Gate::define('auth',function (){
           return  (Auth::check() && Auth::user()?->hasRole())
               || (Auth::check() && Auth::user()?->isAdmin());
        });

        Gate::define('sibAdminUser',function (){
            return Auth::check() && Auth::user()?->hasRole();
        });

        Gate::define('admin',function (){
            return Auth::check() && Auth::user()?->isAdmin();
        });

        Gate::define('owner',function (){
            return Auth::check() && Auth::user()?->isOwner();
        });


    }
}
