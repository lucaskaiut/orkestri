<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeServiceCommand extends Command
{
    protected $signature = 'orkestri:make-service {module} {name?}';
    protected $description = 'Create a new Service following Orkestri architecture';

    public function handle(): void
    {
        $name = $this->argument('module');

        app(ModuleService::class)->createService($name);

        $this->info("Service {$name} created successfully.");
    }
}
