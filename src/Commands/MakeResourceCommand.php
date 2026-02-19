<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeResourceCommand extends Command
{
    protected $signature = 'orkestri:make-resource {module} {name?}';
    protected $description = 'Create a new Resource following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $resourceName = $this->argument('name')
            ? Str::studly($this->argument('name'))
            : "{$module}Resource";

        $basePath = config('orkestri.base_path', 'Modules');

        $resourcePath = base_path(
            "app/{$basePath}/{$module}/Http/Resources/{$resourceName}.php"
        );

        if (File::exists($resourcePath)) {
            $this->error("Resource already exists.");
            return;
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$module}/Http/Resources")
        );

        $stub = $this->getStub();

        $content = str_replace(
            ['{{ namespace }}', '{{ resource }}'],
            [
                "App\\{$basePath}\\{$module}\\Http\\Resources",
                $resourceName,
            ],
            $stub
        );

        File::put($resourcePath, $content);

        $this->info("Resource {$resourceName} created successfully.");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/resource.stub');
    }
}
