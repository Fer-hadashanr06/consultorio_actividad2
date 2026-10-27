<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        // Paginación con HTML simple (sin depender de Tailwind)
        Paginator::useBootstrapThree();

        // Mensajes de validación y fechas en español
        app()->setLocale('es');
    }
}
