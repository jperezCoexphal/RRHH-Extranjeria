<?php

namespace App\Providers;

use App\Repositories\Contracts\EmployerRepository;
use App\Repositories\Eloquent\EloquentEmployerRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       App::bind(EmployerRepository::class, EloquentEmployerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
