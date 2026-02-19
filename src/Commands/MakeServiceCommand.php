<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'orkestri:make-service {module} {name?}';
    protected $description = 'Create a new Service following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $serviceName = $this->argument('name')
            ? Str::studly($this->argument('name'))
            : "{$module}Service";

        $basePath = config('orkestri.base_path', 'Modules');

        $servicePath = base_path(
            "app/{$basePath}/{$module}/Domain/Services/{$serviceName}.php"
        );

        if (File::exists($servicePath)) {
            $this->error("Service already exists.");
            return;
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$module}/Domain/Services")
        );

        $stub = $this->getStub();

        $content = str_replace(
            ['{{ namespace }}', '{{ module }}', '{{ service }}'],
            [
                "App\\{$basePath}\\{$module}\\Domain\\Services",
                $module,
                $serviceName
            ],
            $stub
        );

        File::put($servicePath, $content);

        $this->info("Service {$serviceName} created successfully.");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/service.stub');
    }
}
