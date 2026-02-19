<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $basePath = config('orkestri.base_path', 'Modules');
    $modulesPath = app_path($basePath);
    if (is_dir($modulesPath)) {
        foreach (scandir($modulesPath) as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir("{$modulesPath}/{$dir}")) {
                File::deleteDirectory("{$modulesPath}/{$dir}");
            }
        }
    }
});

afterEach(function () {
    $basePath = config('orkestri.base_path', 'Modules');
    $modulesPath = app_path($basePath);
    if (is_dir($modulesPath)) {
        foreach (scandir($modulesPath) as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir("{$modulesPath}/{$dir}")) {
                File::deleteDirectory("{$modulesPath}/{$dir}");
            }
        }
    }
});

it('registers orkestri:make-module command', function () {
    $this->artisan('list')
        ->expectsOutputToContain('orkestri:make-module')
        ->assertExitCode(0);
});

it('creates module directory structure', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer'))->toBeDirectory();
    expect(app_path('Modules/Customer/Domain/Models'))->toBeDirectory();
    expect(app_path('Modules/Customer/Domain/Services'))->toBeDirectory();
    expect(app_path('Modules/Customer/Database/Migrations'))->toBeDirectory();
    expect(app_path('Modules/Customer/Http/Controllers'))->toBeDirectory();
    expect(app_path('Modules/Customer/Http/Requests'))->toBeDirectory();
    expect(app_path('Modules/Customer/Http/Resources'))->toBeDirectory();
});

it('creates routes api file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Http/Routes/api.php'))->toBeFile();
});

it('creates controller file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Http/Controllers/CustomerController.php'))->toBeFile();
});

it('creates request file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Http/Requests/CustomerRequest.php'))->toBeFile();
});

it('creates resource file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Http/Resources/CustomerResource.php'))->toBeFile();
});

it('creates model file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Domain/Models/Customer.php'))->toBeFile();
});

it('creates service file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Domain/Services/CustomerService.php'))->toBeFile();
});

it('creates migration file', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    $migrations = glob(app_path('Modules/Customer/Database/Migrations/*_create_customers_table.php'));
    expect($migrations)->toHaveCount(1);
    expect($migrations[0])->toBeFile();
});

it('generates migration with correct table name', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    $migrations = glob(app_path('Modules/Customer/Database/Migrations/*_create_customers_table.php'));
    $content = file_get_contents($migrations[0]);
    expect($content)->toContain("Schema::create('customers'");
    expect($content)->toContain("Schema::dropIfExists('customers'");
});

it('generates model with correct namespace', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    $content = file_get_contents(app_path('Modules/Customer/Domain/Models/Customer.php'));
    expect($content)->toContain('namespace App\Modules\Customer\Domain\Models;');
});

it('generates routes with correct pluralized resource name', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    $content = file_get_contents(app_path('Modules/Customer/Http/Routes/api.php'));
    expect($content)->toContain("Route::apiResource('customers'");
});

it('does not overwrite when module already exists and returns error exit code', function () {
    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(1);
});

it('respects custom base_path from config', function () {
    config()->set('orkestri.base_path', 'Domains');

    $this->artisan('orkestri:make-module', ['name' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Domains/Customer'))->toBeDirectory();
    expect(app_path('Domains/Customer/Http/Routes/api.php'))->toBeFile();

    if (File::isDirectory(app_path('Domains/Customer'))) {
        File::deleteDirectory(app_path('Domains/Customer'));
    }
    config()->set('orkestri.base_path', 'Modules');
});
