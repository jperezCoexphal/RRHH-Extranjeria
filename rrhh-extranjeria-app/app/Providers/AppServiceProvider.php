<?php

namespace App\Providers;

use App\Repositories\Contracts\EmployerRepository;
use App\Repositories\Contracts\FileRequirementRepository;
use App\Repositories\Contracts\ForeignerRepository;
use App\Repositories\Contracts\InmigrationFileRepository;
use App\Repositories\Contracts\RequirementTemplateRepository;
use App\Repositories\Eloquent\EloquentEmployerRepository;
use App\Repositories\Eloquent\EloquentFileRequirementRepository;
use App\Repositories\Eloquent\EloquentForeignerRepository;
use App\Repositories\Eloquent\EloquentInmigrationFileRepository;
use App\Repositories\Eloquent\EloquentRequirementTemplateRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositorios de entidades base
        App::bind(EmployerRepository::class, EloquentEmployerRepository::class);
        App::bind(ForeignerRepository::class, EloquentForeignerRepository::class);

        // Repositorios de expedientes y requisitos
        App::bind(InmigrationFileRepository::class, EloquentInmigrationFileRepository::class);
        App::bind(RequirementTemplateRepository::class, EloquentRequirementTemplateRepository::class);
        App::bind(FileRequirementRepository::class, EloquentFileRequirementRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
