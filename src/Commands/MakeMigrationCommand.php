<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeMigrationCommand extends Command
{
    protected $signature = 'orkestri:make-migration {module} {name?}';
    protected $description = 'Create a new migration for the module following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $basePath = config('orkestri.base_path', 'Modules');

        $migrationName = $this->argument('name')
            ? Str::snake($this->argument('name'))
            : 'create_' . Str::snake(Str::pluralStudly($module)) . '_table';

        $tableName = $this->argument('name')
            ? $this->getTableNameFromMigrationName($migrationName)
            : Str::snake(Str::pluralStudly($module));

        $migrationsDirectory = base_path(
            "app/{$basePath}/{$module}/Database/Migrations"
        );

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$migrationName}.php";
        $migrationPath = "{$migrationsDirectory}/{$fileName}";

        if (File::exists($migrationPath)) {
            $this->error("Migration already exists.");
            return;
        }

        File::ensureDirectoryExists($migrationsDirectory);

        $stub = $this->getStub();

        $content = str_replace('{{ table }}', $tableName, $stub);

        File::put($migrationPath, $content);

        $this->info("Migration {$fileName} created successfully.");
    }

    protected function getTableNameFromMigrationName(string $name): string
    {
        if (Str::startsWith($name, 'create_') && Str::endsWith($name, '_table')) {
            return Str::substr($name, 7, -7);
        }

        return Str::plural(Str::snake($name));
    }

    protected function getStub(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/migration.stub');
    }
}
