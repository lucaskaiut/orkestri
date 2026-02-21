<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeRoutesCommand extends Command
{
    protected $signature = 'orkestri:make-routes {module}';
    protected $description = 'Create api routes file for the module following Orkestri architecture';

    public function handle(): void
    {
        $name = $this->argument('module');

        app(ModuleService::class)->createRoutes($name);

        $this->info("Routes file created successfully.");
    }
}
