<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeControllerCommand extends Command
{
    protected $signature = 'orkestri:make-controller {module} {name?}';
    protected $description = 'Create a new Controller following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $controllerName = $this->argument('name')
            ? Str::studly($this->argument('name'))
            : "{$module}Controller";

        $basePath = config('orkestri.base_path', 'Modules');

        $controllerPath = base_path(
            "app/{$basePath}/{$module}/Http/Controllers/{$controllerName}.php"
        );

        if (File::exists($controllerPath)) {
            $this->error("Controller already exists.");
            return;
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$module}/Http/Controllers")
        );

        $stub = $this->getStub();

        $serviceName = "{$module}Service";
        $resourceName = "{$module}Resource";
        $requestName = "{$module}Request";

        $content = str_replace(
            [
                '{{ namespace }}',
                '{{ basePath }}',
                '{{ module }}',
                '{{ controller }}',
                '{{ service }}',
                '{{ resource }}',
                '{{ request }}',
            ],
            [
                "App\\{$basePath}\\{$module}\\Http\\Controllers",
                $basePath,
                $module,
                $controllerName,
                $serviceName,
                $resourceName,
                $requestName,
            ],
            $stub
        );

        File::put($controllerPath, $content);

        $this->info("Controller {$controllerName} created successfully.");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/controller.stub');
    }
}
