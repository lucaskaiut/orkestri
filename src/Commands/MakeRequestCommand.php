<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use LucasKaiut\Orkestri\Services\ModuleService;

class MakeRequestCommand extends Command
{
    protected $signature = 'orkestri:make-request {module} {name?}';
    protected $description = 'Create a new Request following Orkestri architecture';

    public function handle(): void
    {
        $name = $this->argument('module');

        app(ModuleService::class)->createRequest($name);

        $this->info("Request {$name} created successfully.");
    }
}
