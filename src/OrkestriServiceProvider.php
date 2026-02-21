<?php

namespace LucasKaiut\Orkestri;

use Illuminate\Support\ServiceProvider;

class OrkestriServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/orkestri.php',
            'orkestri'
        );
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../languages', 'orkestri');
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'orkestri'
        );

        $this->publishes([
            __DIR__ . '/../config/orkestri.php' => config_path('orkestri.php'),
        ], 'orkestri-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \LucasKaiut\Orkestri\Commands\MakeModuleCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeServiceCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeModelCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeControllerCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeResourceCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeRequestCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeRoutesCommand::class,
                \LucasKaiut\Orkestri\Commands\MakeMigrationCommand::class,
            ]);
        }
    }

    protected function loadModuleRoutes(): void
    {
        $basePath = config('orkestri.base_path', 'Modules');
        $modulesPath = app_path($basePath);

        if (!is_dir($modulesPath)) {
            return;
        }

        foreach (scandir($modulesPath) as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            $routeFile = "{$modulesPath}/{$module}/Http/Routes/api.php";

            if (file_exists($routeFile)) {
                $this->loadRoutesFrom($routeFile);
            }
        }
    }

    protected function loadModuleMigrations(): void
    {
        $basePath = config('orkestri.base_path', 'Modules');
        $modulesPath = app_path($basePath);

        if (!is_dir($modulesPath)) {
            return;
        }

        foreach (scandir($modulesPath) as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            $migrationsPath = "{$modulesPath}/{$module}/Database/Migrations";

            if (is_dir($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }
}
