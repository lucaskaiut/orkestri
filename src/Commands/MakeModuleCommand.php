<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeModuleCommand extends Command
{
    protected $signature = 'orkestri:make-module {name}';
    protected $description = 'Create a new module following Orkestri architecture';

    public function handle(): int
    {
        $name = $this->argument('name');
        
        app(ModuleService::class)->createModule($name);

        $this->info("Module {$name} created successfully.");

        return 0;
    }
}
