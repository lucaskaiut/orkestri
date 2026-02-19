<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRoutesCommand extends Command
{
    protected $signature = 'orkestri:make-routes {module}';
    protected $description = 'Create api routes file for the module following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $basePath = config('orkestri.base_path', 'Modules');

        $routesPath = base_path(
            "app/{$basePath}/{$module}/Http/Routes/api.php"
        );

        if (File::exists($routesPath)) {
            $this->error("Routes file already exists.");
            return;
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$module}/Http/Routes")
        );

        $stub = $this->getStub();

        $controllerName = "{$module}Controller";
        $resourceName = Str::plural(Str::snake($module));

        $content = str_replace(
            [
                '{{ basePath }}',
                '{{ module }}',
                '{{ controller }}',
                '{{ resourceName }}',
            ],
            [
                $basePath,
                $module,
                $controllerName,
                $resourceName,
            ],
            $stub
        );

        File::put($routesPath, $content);

        $this->info("Routes file created successfully.");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/routes-api.stub');
    }
}
