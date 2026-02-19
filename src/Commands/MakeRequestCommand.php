<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    protected $signature = 'orkestri:make-request {module} {name?}';
    protected $description = 'Create a new Request following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $requestName = $this->argument('name')
            ? Str::studly($this->argument('name'))
            : "{$module}Request";

        $basePath = config('orkestri.base_path', 'Modules');

        $requestPath = base_path(
            "app/{$basePath}/{$module}/Http/Requests/{$requestName}.php"
        );

        if (File::exists($requestPath)) {
            $this->error("Request already exists.");
            return;
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$module}/Http/Requests")
        );

        $stub = $this->getStub();

        $content = str_replace(
            ['{{ namespace }}', '{{ request }}'],
            [
                "App\\{$basePath}\\{$module}\\Http\\Requests",
                $requestName,
            ],
            $stub
        );

        File::put($requestPath, $content);

        $this->info("Request {$requestName} created successfully.");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/request.stub');
    }
}
