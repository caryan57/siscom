<?php

namespace App\Providers;

use App\Models\Role;
use App\Observers\RoleObserver;
use App\Services\CodeGeneratorService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Code generator servie
        $this->app->singleton(CodeGeneratorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observers
        Role::observe(RoleObserver::class);
    }
}
