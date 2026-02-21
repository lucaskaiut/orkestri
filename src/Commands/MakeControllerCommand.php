<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeControllerCommand extends Command
{
    protected $signature = 'orkestri:make-controller {module} {name?}';
    protected $description = 'Create a new Controller following Orkestri architecture';

    public function handle(): void
    {
        $name = $this->argument('module');

        app(ModuleService::class)->createController($name);

        $this->info("Controller {$name} created successfully.");
    }
}
