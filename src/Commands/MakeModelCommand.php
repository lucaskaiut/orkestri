<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModelCommand extends Command
{
    protected $signature = 'orkestri:make-model {module} {name?}';
    protected $description = 'Create a new Model following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $modelName = $this->argument('name')
            ? Str::studly($this->argument('name'))
            : $module;

        $basePath = config('orkestri.base_path', 'Modules');

        $modelDirectory = base_path(
            "app/{$basePath}/{$module}/Domain/Models"
        );

        $modelPath = "{$modelDirectory}/{$modelName}.php";

        if (File::exists($modelPath)) {
            $this->error("Model already exists.");
            return;
        }

        File::ensureDirectoryExists($modelDirectory);

        $stub = $this->getStub();

        $namespace = "App\\{$basePath}\\{$module}\\Domain\\Models";
        $table = Str::snake(Str::pluralStudly($modelName));

        $content = str_replace(
            ['{{ namespace }}', '{{ model }}', '{{ table }}'],
            [$namespace, $modelName, $table],
            $stub
        );

        File::put($modelPath, $content);

        $this->info("Model {$modelName} created successfully.");
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/model.stub');
    }
}
