<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeMigrationCommand extends Command
{
    protected $signature = 'orkestri:make-migration {module} {name?}';
    protected $description = 'Create a new migration for the module following Orkestri architecture';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));

        app(ModuleService::class)->createMigration($module);

        $this->info("Migration {$module} created successfully.");
    }
}
