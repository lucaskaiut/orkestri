<?php

namespace LucasKaiut\Orkestri\Services;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LucasKaiut\Orkestri\Contracts\ServiceContract;
use LucasKaiut\Orkestri\Models\Module;
use LucasKaiut\Orkestri\Models\ModuleField;
use LucasKaiut\Orkestri\Traits\ServiceTrait;

class ModuleService implements ServiceContract
{
    use ServiceTrait;

    protected string $model = Module::class;

    public function create(array $attributes): Module
    {
        $module = $this->model()->create($attributes);

        $fields = $module->fields()->createMany($attributes['fields'] ?? []);

        $this->createModule($module->name, $fields);

        return $module;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);

        $this->deleteModule($model->name);

        return (bool) $model->delete();
    }

    public function runMigration(Module $module): void
    {
        $migrationFiles = $this->getMigrationFilesFromModuleName($module->name);

        foreach ($migrationFiles as $file) {
            $this->executeMigrationFile($file->getPathname());
        }

        $module->migration_status = 'migrated';
        $module->save();
    }

    private function executeMigrationFile(string $filePath): void
    {
        $migration = require $filePath;

        if (! $migration instanceof Migration) {
            throw new \RuntimeException("File does not return a valid Migration instance: {$filePath}");
        }

        $migration->up();
    }

    private function deleteModule(string $name): void
    {
        $basePath = config('orkestri.base_path');

        $modulePath = base_path("app/{$basePath}/{$name}");

        $this->dropMigration($name);

        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }
    }

    private function dropMigration(string $name): void
    {
        $migrationFiles = $this->getMigrationFilesFromModuleName($name);

        foreach ($migrationFiles as $file) {
            $tableName = $this->resolveTableNameFromMigration($file->getPathname());

            if ($tableName) {
                Schema::dropIfExists($tableName);
            }

            File::delete($file->getPathname());
        }
    }

    private function getMigrationFilesFromModuleName(string $name): array
    {
        $basePath = config('orkestri.base_path');
        $migrationsDirectory = base_path("app/{$basePath}/{$name}/Database/Migrations");

        if (!File::exists($migrationsDirectory)) {
            return [];
        }

        return File::files($migrationsDirectory);
    }

    private function resolveTableNameFromMigration(string $filePath): ?string
    {
        $content = File::get($filePath);

        preg_match("/Schema::create\(['\"]([^'\"]+)['\"]/", $content, $matches);

        return $matches[1] ?? null;
    }

    public function createModule(string $name, $fields = [])
    {
        $basePath = config('orkestri.base_path');

        $modulePath = base_path("app/{$basePath}/{$name}");

        if (File::exists($modulePath)) {
            throw new \Exception("Module already exists.");
        }

        foreach (config('orkestri.structure') as $folder) {
            File::makeDirectory("{$modulePath}/{$folder}", 0755, true);
        }

        $this->createStructure($name, $fields);
    }

    public function createStructure(string $name, $fields = [])
    {
        $this->createService($name);
        $this->createModel($name);
        $this->createMigration($name, $fields);
        $this->createController($name);
        $this->createResource($name);
        $this->createRequest($name, $fields);
        $this->createRoutes($name);
    }

    public function createService($name)
    {
        $name = Str::studly($name);

        $serviceName = "{$name}Service";

        $basePath = config('orkestri.base_path', 'Modules');

        $servicePath = base_path(
            "app/{$basePath}/{$name}/Domain/Services/{$serviceName}.php"
        );

        if (File::exists($servicePath)) {
            throw new \Exception("Service already exists.");
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$name}/Domain/Services")
        );

        $stub = file_get_contents(__DIR__ . '/../../stubs/service.stub');

        $content = str_replace(
            ['{{ namespace }}', '{{ module }}', '{{ service }}'],
            [
                "App\\{$basePath}\\{$name}\\Domain\\Services",
                $name,
                $serviceName
            ],
            $stub
        );

        File::put($servicePath, $content);
    }

    public function createModel($name)
    {
        $basePath = config('orkestri.base_path', 'Modules');

        $modelDirectory = base_path(
            "app/{$basePath}/{$name}/Domain/Models"
        );

        $modelPath = "{$modelDirectory}/{$name}.php";

        if (File::exists($modelPath)) {
            throw new \Exception("Model already exists.");
        }

        File::ensureDirectoryExists($modelDirectory);

        $stub = file_get_contents(__DIR__ . '/../../stubs/model.stub');

        $namespace = "App\\{$basePath}\\{$name}\\Domain\\Models";
        $table = Str::snake(Str::pluralStudly($name));

        $content = str_replace(
            ['{{ namespace }}', '{{ model }}', '{{ table }}'],
            [$namespace, $name, $table],
            $stub
        );

        File::put($modelPath, $content);
    }

    public function createMigration(string $name, $fields = []): void
    {
        $studlyName = Str::studly($name);
        $tableName = Str::snake(Str::pluralStudly($name));
        $basePath = config('orkestri.base_path', 'Modules');

        $migrationsDirectory = base_path("app/{$basePath}/{$studlyName}/Database/Migrations");
        $migrationPath = $this->resolveMigrationPath($migrationsDirectory, $tableName);

        if (File::exists($migrationPath)) {
            throw new \RuntimeException("Migration already exists: {$migrationPath}");
        }

        File::ensureDirectoryExists($migrationsDirectory);

        $content = $this->buildMigrationContent($tableName, $fields);

        File::put($migrationPath, $content);
    }

    private function resolveMigrationPath(string $directory, string $tableName): string
    {
        $timestamp = now()->format('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";

        return "{$directory}/{$fileName}";
    }

    private function buildMigrationContent(string $tableName, $fields): string
    {
        $stub = File::get(__DIR__ . '/../../stubs/migration.stub');

        return str_replace(
            ['{{ table }}', '{{ fields }}'],
            [$tableName, $this->buildFieldDefinitions($fields)],
            $stub
        );
    }

    private function buildFieldDefinitions(iterable $fields): string
    {
        $lines = collect($fields)
            ->map(fn(ModuleField $field) => $this->buildFieldDefinition($field))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            return '';
        }

        return $lines
            ->map(fn(string $line) => str_repeat(' ', 12) . $line)
            ->implode("\n");
    }

    private function buildFieldDefinition(ModuleField $field): ?string
    {
        $name = $field->name ?? null;
        $type = $field->type ?? null;

        if (!$name || !$type) {
            return null;
        }

        $definition = "\$table->{$type}('{$name}')";

        if (!!$field->nullable && !$field->required) {
            $definition .= '->nullable()';
        }

        return $definition . ';';
    }

    public function createController($name)
    {
        $name = Str::studly($name);
        $controllerName = "{$name}Controller";

        $basePath = config('orkestri.base_path', 'Modules');

        $controllerPath = base_path(
            "app/{$basePath}/{$name}/Http/Controllers/{$controllerName}.php"
        );

        if (File::exists($controllerPath)) {
            throw new \Exception("Controller already exists.");
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$name}/Http/Controllers")
        );

        $stub = file_get_contents(__DIR__ . '/../../stubs/controller.stub');

        $serviceName = "{$name}Service";
        $resourceName = "{$name}Resource";
        $requestName = "{$name}Request";

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
                "App\\{$basePath}\\{$name}\\Http\\Controllers",
                $basePath,
                $name,
                $controllerName,
                $serviceName,
                $resourceName,
                $requestName,
            ],
            $stub
        );

        File::put($controllerPath, $content);
    }

    public function createResource($name)
    {
        $name = Str::studly($name);
        $resourceName = "{$name}Resource";

        $basePath = config('orkestri.base_path', 'Modules');

        $resourcePath = base_path(
            "app/{$basePath}/{$name}/Http/Resources/{$resourceName}.php"
        );

        if (File::exists($resourcePath)) {
            throw new \Exception("Resource already exists.");
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$name}/Http/Resources")
        );

        $stub = file_get_contents(__DIR__ . '/../../stubs/resource.stub');

        $content = str_replace(
            ['{{ namespace }}', '{{ resource }}'],
            [
                "App\\{$basePath}\\{$name}\\Http\\Resources",
                $resourceName,
            ],
            $stub
        );

        File::put($resourcePath, $content);
    }

    public function createRequest(string $name, $fields = []): void
    {
        $studlyName = Str::studly($name);
        $requestName = "{$studlyName}Request";
        $basePath = config('orkestri.base_path', 'Modules');

        $requestPath = base_path("app/{$basePath}/{$studlyName}/Http/Requests/{$requestName}.php");

        if (File::exists($requestPath)) {
            throw new \RuntimeException("Request already exists: {$requestPath}");
        }

        File::ensureDirectoryExists(dirname($requestPath));

        $content = $this->buildRequestContent($studlyName, $requestName, $basePath, $fields);

        File::put($requestPath, $content);
    }

    private function buildRequestContent(string $name, string $requestName, string $basePath, $fields): string
    {
        $stub = File::get(__DIR__ . '/../../stubs/request.stub');

        return str_replace(
            ['{{ namespace }}', '{{ request }}', '{{ rules }}', '{{ prepareForValidation }}'],
            [
                "App\\{$basePath}\\{$name}\\Http\\Requests",
                $requestName,
                $this->buildRules($fields),
                $this->buildPrepareForValidation($fields),
            ],
            $stub
        );
    }

    private function buildRules(iterable $fields): string
    {
        $rules = [];
        
        foreach ($fields as $field) {
            $name = $field->name ?? null;
            $type = $field->type ?? null;

            if (!$name || !$type) {
                continue;
            }

            $fieldRules = $this->resolveFieldRules($field);
            $rulesLine  = "['" . implode("', '", $fieldRules) . "']";
            $rules[] = "'{$name}' => {$rulesLine},";
        }

        return $this->indentLines($rules, 3);
    }

    private function resolveFieldRules(ModuleField $field): array
    {
        $type = $field->type;
        $nullable = !empty($field->nullable);
        $required = !empty($field->required);

        $rules = [$nullable ? 'nullable' : 'required'];

        $rules[] = match (true) {
            in_array($type, ['integer', 'bigInteger']) => 'integer',
            in_array($type, ['float', 'decimal']) => 'numeric',
            $type === 'boolean' => 'boolean',
            in_array($type, ['date', 'dateTime', 'timestamp']) => 'date',
            $type === 'json' => 'array',
            default => 'string',
        };

        if ($required && $nullable) {
            $rules[] = 'sometimes';
        }

        return $rules;
    }

    private function buildPrepareForValidation(iterable $fields): string
    {
        $booleanFields = collect($fields)
            ->filter(fn(ModuleField $field) => ($field->type ?? null) === 'boolean')
            ->pluck('name')
            ->filter()
            ->values();

        if ($booleanFields->isEmpty()) {
            return '';
        }

        $merges = $booleanFields
            ->map(fn(string $name) => "'{$name}' => filter_var(\$this->input('{$name}'), FILTER_VALIDATE_BOOLEAN),")
            ->all();

        $lines = array_merge(
            ['$this->merge(['],
            $this->indentLines($merges, 4, false),
            ['        ]);']
        );

        $body = implode("\n", $this->indentLines($lines, 2, false));

        return "\n    protected function prepareForValidation(): void\n    {\n{$body}\n    }\n";
    }

    private function indentLines(array $lines, int $tabs, bool $asString = true): string|array
    {
        $indent = str_repeat('    ', $tabs);
        $indented = array_map(fn(string $line) => "{$indent}{$line}", $lines);

        return $asString ? implode("\n", $indented) : $indented;
    }

    public function createRoutes($name)
    {
        $name = Str::studly($name);
        $basePath = config('orkestri.base_path', 'Modules');

        $routesPath = base_path(
            "app/{$basePath}/{$name}/Http/Routes/api.php"
        );

        if (File::exists($routesPath)) {
            throw new \Exception("Routes already exists.");
        }

        File::ensureDirectoryExists(
            base_path("app/{$basePath}/{$name}/Http/Routes")
        );

        $stub = file_get_contents(__DIR__ . '/../../stubs/routes-api.stub');

        $controllerName = "{$name}Controller";
        $resourceName = Str::plural(Str::snake($name));

        $content = str_replace(
            [
                '{{ basePath }}',
                '{{ module }}',
                '{{ controller }}',
                '{{ resourceName }}',
            ],
            [
                $basePath,
                $name,
                $controllerName,
                $resourceName,
            ],
            $stub
        );

        File::put($routesPath, $content);
    }
}
