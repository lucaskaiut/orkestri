<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeModelCommand extends Command
{
    protected $signature = 'orkestri:make-model {module} {name?}';
    protected $description = 'Create a new Model following Orkestri architecture';

    public function handle(): void
    {
        $name = $this->argument('module');

        app(ModuleService::class)->createModule($name);

        $this->info("Model {$name} created successfully.");
    }
}
