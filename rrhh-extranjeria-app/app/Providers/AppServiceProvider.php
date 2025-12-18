<?php

namespace App\Providers;

use App\Repositories\Contracts\EmployerRepository;
use App\Repositories\Contracts\ForeignerRepository;
use App\Repositories\Eloquent\EloquentEmployerRepository;
use App\Repositories\Eloquent\EloquentForeignerRepository;
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
       App::bind(ForeignerRepository::class, EloquentForeignerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
