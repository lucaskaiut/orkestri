<?php

namespace LucasKaiut\Orkestri\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected $signature = 'orkestri:make-module {name}';
    protected $description = 'Create a new module following Orkestri architecture';

    public function handle(): int
    {
        $name = $this->argument('name');
        $basePath = config('orkestri.base_path');

        $modulePath = base_path("app/{$basePath}/{$name}");

        if (File::exists($modulePath)) {
            $this->error("Module already exists.");
            return 1;
        }

        foreach (config('orkestri.structure') as $folder) {
            File::makeDirectory("{$modulePath}/{$folder}", 0755, true);
        }

        $this->call('orkestri:make-service', ['module' => $name]);
        $this->call('orkestri:make-model', ['module' => $name]);
        $this->call('orkestri:make-migration', ['module' => $name]);
        $this->call('orkestri:make-controller', ['module' => $name]);
        $this->call('orkestri:make-resource', ['module' => $name]);
        $this->call('orkestri:make-request', ['module' => $name]);
        $this->call('orkestri:make-routes', ['module' => $name]);

        $this->info("Module {$name} created successfully.");

        return 0;
    }
}
