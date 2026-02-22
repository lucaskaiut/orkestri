<?php

namespace LucasKaiut\Orkestri\Services;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LucasKaiut\Orkestri\Contracts\ServiceContract;
use LucasKaiut\Orkestri\Models\Module;
use LucasKaiut\Orkestri\Models\ModuleField;
use LucasKaiut\Orkestri\Models\ModuleRelationship;
use LucasKaiut\Orkestri\Traits\ServiceTrait;

class ModuleService implements ServiceContract
{
    use ServiceTrait;

    protected string $model = Module::class;

    public function create(array $attributes): Module
    {
        $module = $this->model()->create($attributes);

        $fields = $module->fields()->createMany($attributes['fields'] ?? []);
        $relationships = $module->relationships()->createMany($attributes['relationships'] ?? []);

        $this->createModule($module->name, $fields, $relationships);

        return $module;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);

        $this->deleteModule($model->name);

        return (bool)$model->delete();
    }

    public function runMigration(Module $module): void
    {
        foreach ($this->getMigrationFilesFromModuleName($module->name) as $file) {
            $this->executeMigrationFile($file->getPathname());
        }

        $module->update(['migration_status' => 'migrated']);
    }

    public function createModule(string $name, iterable $fields = [], iterable $relationships = []): void
    {
        $modulePath = $this->resolveModulePath($name);

        if (File::exists($modulePath)) {
            throw new \RuntimeException("Module already exists: {$name}");
        }

        foreach (config('orkestri.structure') as $folder) {
            File::makeDirectory("{$modulePath}/{$folder}", 0755, true);
        }

        $this->createStructure($name, $fields, $relationships);
    }

    public function createStructure(string $name, iterable $fields = [], iterable $relationships = []): void
    {
        $this->createMigration($name, $fields, $relationships);
        $this->createModel($name, $relationships);
        $this->createService($name);
        $this->createController($name);
        $this->createResource($name);
        $this->createRequest($name, $fields);
        $this->createRoutes($name);
    }

    public function createService(string $name): void
    {
        $studly = Str::studly($name);
        $serviceName = "{$studly}Service";
        $basePath = $this->basePath();

        $path = base_path("app/{$basePath}/{$studly}/Domain/Services/{$serviceName}.php");

        $this->guardFileExists($path, 'Service');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->compileStub('service', [
            '{{ namespace }}' => "App\\{$basePath}\\{$studly}\\Domain\\Services",
            '{{ module }}' => $studly,
            '{{ service }}' => $serviceName,
        ]));
    }

    public function createModel(string $name, iterable $relationships = []): void
    {
        $studly = Str::studly($name);
        $basePath = $this->basePath();
        $path = base_path("app/{$basePath}/{$studly}/Domain/Models/{$studly}.php");

        $this->guardFileExists($path, 'Model');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->compileStub('model', [
            '{{ namespace }}' => "App\\{$basePath}\\{$studly}\\Domain\\Models",
            '{{ model }}' => $studly,
            '{{ table }}' => Str::snake(Str::pluralStudly($studly)),
            '{{ relationships }}' => $this->buildModelRelationships($relationships, $basePath),
        ]));
    }

    public function createMigration(string $name, iterable $fields = [], iterable $relationships = []): void
    {
        $studly = Str::studly($name);
        $tableName = Str::snake(Str::pluralStudly($studly));
        $basePath = $this->basePath();

        $directory = base_path("app/{$basePath}/{$studly}/Database/Migrations");
        $path = $this->resolveMigrationPath($directory, $tableName);

        $this->guardFileExists($path, 'Migration');

        File::ensureDirectoryExists($directory);

        File::put($path, $this->compileStub('migration', [
            '{{ table }}' => $tableName,
            '{{ fields }}' => $this->buildFieldDefinitions($fields, $relationships),
        ]));
    }

    public function createController(string $name): void
    {
        $studly = Str::studly($name);
        $controllerName = "{$studly}Controller";
        $basePath = $this->basePath();

        $path = base_path("app/{$basePath}/{$studly}/Http/Controllers/{$controllerName}.php");

        $this->guardFileExists($path, 'Controller');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->compileStub('controller', [
            '{{ namespace }}' => "App\\{$basePath}\\{$studly}\\Http\\Controllers",
            '{{ basePath }}' => $basePath,
            '{{ module }}' => $studly,
            '{{ controller }}' => $controllerName,
            '{{ service }}' => "{$studly}Service",
            '{{ resource }}' => "{$studly}Resource",
            '{{ request }}' => "{$studly}Request",
        ]));
    }

    public function createResource(string $name): void
    {
        $studly = Str::studly($name);
        $resourceName = "{$studly}Resource";
        $basePath = $this->basePath();

        $path = base_path("app/{$basePath}/{$studly}/Http/Resources/{$resourceName}.php");

        $this->guardFileExists($path, 'Resource');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->compileStub('resource', [
            '{{ namespace }}' => "App\\{$basePath}\\{$studly}\\Http\\Resources",
            '{{ resource }}' => $resourceName,
        ]));
    }

    public function createRequest(string $name, iterable $fields = []): void
    {
        $studly = Str::studly($name);
        $requestName = "{$studly}Request";
        $basePath = $this->basePath();

        $path = base_path("app/{$basePath}/{$studly}/Http/Requests/{$requestName}.php");

        $this->guardFileExists($path, 'Request');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->compileStub('request', [
            '{{ namespace }}' => "App\\{$basePath}\\{$studly}\\Http\\Requests",
            '{{ request }}' => $requestName,
            '{{ rules }}' => $this->buildRules($fields),
            '{{ prepareForValidation }}' => $this->buildPrepareForValidation($fields),
        ]));
    }

    public function createRoutes(string $name): void
    {
        $studly = Str::studly($name);
        $basePath = $this->basePath();
        $resourceName = Str::plural(Str::snake($studly));

        $path = base_path("app/{$basePath}/{$studly}/Http/Routes/api.php");

        $this->guardFileExists($path, 'Routes');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->compileStub('routes-api', [
            '{{ basePath }}' => $basePath,
            '{{ module }}' => $studly,
            '{{ controller }}' => "{$studly}Controller",
            '{{ resourceName }}' => $resourceName,
        ]));
    }

    private function buildFieldDefinitions(iterable $fields, iterable $relationships = []): string
    {
        $fieldLines = $this->resolveFieldLines($fields);
        $foreignKeyLines = $this->resolveForeignKeyLines($relationships);

        $allLines = [...$fieldLines, ...$foreignKeyLines];

        return collect($allLines)
            ->map(fn(string $line) => str_repeat(' ', 12) . $line)
            ->implode("\n");
    }

    private function resolveFieldLines(iterable $fields): array
    {
        return collect($fields)
            ->map(fn(ModuleField $field) => $this->buildFieldLine($field))
            ->filter()
            ->values()
            ->all();
    }

    private function buildFieldLine(ModuleField $field): ?string
    {
        if (!$field->name || !$field->type) {
            return null;
        }

        $definition = "\$table->{$field->type}('{$field->name}')";

        if ($field->nullable && !$field->required) {
            $definition .= '->nullable()';
        }

        return $definition . ';';
    }

    private function resolveForeignKeyLines(iterable $relationships): array
    {
        return collect($relationships)
            ->filter(fn(ModuleRelationship $rel) => $rel->type === 'belongsTo')
            ->flatMap(fn(ModuleRelationship $rel) => $this->buildForeignKeyLines($rel))
            ->values()
            ->all();
    }

    private function buildForeignKeyLines(ModuleRelationship $relationship): array
    {
        $fk = $relationship->foreign_key;
        $ownerKey = $relationship->owner_key ?? 'id';
        $relatedTable = $this->resolveTableNameFromModuleId($relationship->related_module);

        if (!$fk || !$relatedTable) {
            return [];
        }

        return [
            "\$table->unsignedBigInteger('{$fk}');",
            "\$table->foreign('{$fk}')->references('{$ownerKey}')->on('{$relatedTable}')->cascadeOnDelete();",
        ];
    }

    private function resolveTableNameFromModuleId(int|string $moduleId): ?string
    {
        $module = Module::find($moduleId);

        return $module ? Str::snake(Str::pluralStudly($module->name)) : null;
    }

    private function buildModelRelationships(iterable $relationships, string $basePath): string
    {
        $methods = collect($relationships)
            ->map(fn(ModuleRelationship $rel) => $this->buildRelationshipMethod($rel, $basePath))
            ->filter()
            ->implode("\n\n");

        return $methods ? "\n{$methods}\n" : '';
    }

    private function buildRelationshipMethod(ModuleRelationship $relationship, string $basePath): ?string
    {
        $relatedModule = Module::find($relationship->related_module);

        if (!$relatedModule) {
            return null;
        }

        $relatedStudly = Str::studly($relatedModule->name);
        $relatedClass = "\\App\\{$basePath}\\{$relatedStudly}\\Domain\\Models\\{$relatedStudly}";
        $methodName = $relationship->relation_name ?: $this->defaultRelationName($relationship->type, $relatedStudly);

        $fk = $relationship->foreign_key ? "'{$relationship->foreign_key}'" : 'null';
        $ownerKey = $relationship->owner_key ? "'{$relationship->owner_key}'" : 'null';

        $body = match ($relationship->type) {
            'belongsTo' => "return \$this->belongsTo({$relatedClass}::class, {$fk}, {$ownerKey});",
            'hasMany' => "return \$this->hasMany({$relatedClass}::class, {$fk}, {$ownerKey});",
            'hasOne' => "return \$this->hasOne({$relatedClass}::class, {$fk}, {$ownerKey});",
            'belongsToMany' => "return \$this->belongsToMany({$relatedClass}::class);",
            default => null,
        };

        if (!$body) {
            return null;
        }

        $returnType = match ($relationship->type) {
            'belongsTo' => '\\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'hasMany' => '\\Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            'hasOne' => '\\Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'belongsToMany' => '\\Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            default => 'mixed',
        };

        return <<<PHP
    public function {$methodName}(): {$returnType}
    {
        {$body}
    }
PHP;
    }

    private function defaultRelationName(string $type, string $relatedStudly): string
    {
        $snake = Str::snake($relatedStudly);

        return in_array($type, ['hasMany', 'belongsToMany'])
            ? Str::plural($snake)
            : $snake;
    }

    private function buildRules(iterable $fields): string
    {
        $rules = collect($fields)
            ->filter(fn(ModuleField $field) => $field->name && $field->type)
            ->map(function (ModuleField $field): string {
                $fieldRules = $this->resolveFieldRules($field);
                $rulesLine = "['" . implode("', '", $fieldRules) . "']";
                return "'{$field->name}' => {$rulesLine},";
            })
            ->all();

        return $this->indentLines($rules, 3);
    }

    private function resolveFieldRules(ModuleField $field): array
    {
        $nullable = !empty($field->nullable);
        $required = !empty($field->required);

        $rules = [$nullable ? 'nullable' : 'required'];

        $rules[] = match (true) {
            in_array($field->type, ['integer', 'bigInteger']) => 'integer',
            in_array($field->type, ['float', 'decimal']) => 'numeric',
            $field->type === 'boolean' => 'boolean',
            in_array($field->type, ['date', 'dateTime', 'timestamp']) => 'date',
            $field->type === 'json' => 'array',
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

    private function deleteModule(string $name): void
    {
        $this->dropMigration($name);

        $modulePath = $this->resolveModulePath($name);

        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }
    }

    private function dropMigration(string $name): void
    {
        foreach ($this->getMigrationFilesFromModuleName($name) as $file) {
            $tableName = $this->resolveTableNameFromMigrationFile($file->getPathname());

            if ($tableName) {
                Schema::dropIfExists($tableName);
            }

            File::delete($file->getPathname());
        }
    }

    private function executeMigrationFile(string $filePath): void
    {
        $migration = require $filePath;

        if (!$migration instanceof Migration) {
            throw new \RuntimeException("File does not return a valid Migration instance: {$filePath}");
        }

        $migration->up();
    }

    private function basePath(): string
    {
        return config('orkestri.base_path', 'Modules');
    }

    private function resolveModulePath(string $name): string
    {
        return base_path("app/{$this->basePath()}/" . Str::studly($name));
    }

    private function resolveMigrationPath(string $directory, string $tableName): string
    {
        return "{$directory}/{$this->migrationTimestamp()}_create_{$tableName}_table.php";
    }

    private function migrationTimestamp(): string
    {
        return now()->format('Y_m_d_His');
    }

    private function getMigrationFilesFromModuleName(string $name): array
    {
        $directory = base_path("app/{$this->basePath()}/" . Str::studly($name) . '/Database/Migrations');

        return File::exists($directory) ? File::files($directory) : [];
    }

    private function resolveTableNameFromMigrationFile(string $filePath): ?string
    {
        preg_match("/Schema::create\(['\"]([^'\"]+)['\"]/", File::get($filePath), $matches);

        return $matches[1] ?? null;
    }

    private function compileStub(string $stub, array $replacements): string
    {
        $content = File::get(__DIR__ . "/../../stubs/{$stub}.stub");

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function guardFileExists(string $path, string $type): void
    {
        if (File::exists($path)) {
            throw new \RuntimeException("{$type} already exists: {$path}");
        }
    }

    private function indentLines(array $lines, int $tabs, bool $asString = true): string|array
    {
        $indent = str_repeat('    ', $tabs);
        $indented = array_map(fn(string $line) => "{$indent}{$line}", $lines);

        return $asString ? implode("\n", $indented) : $indented;
    }
}
