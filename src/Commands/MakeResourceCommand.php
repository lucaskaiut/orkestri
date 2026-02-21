<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeResourceCommand extends Command
{
    protected $signature = 'orkestri:make-resource {module} {name?}';
    protected $description = 'Create a new Resource following Orkestri architecture';

    public function handle(): void
    {
        $name = $this->argument('module');

        app(ModuleService::class)->createResource($name);

        $this->info("Resource {$name} created successfully.");
    }
}
