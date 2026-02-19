<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $basePath = config('orkestri.base_path', 'Modules');
    $modulePath = app_path("{$basePath}/Customer");
    if (File::isDirectory($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    File::makeDirectory(app_path("{$basePath}/Customer/Domain/Services"), 0755, true);
});

afterEach(function () {
    $basePath = config('orkestri.base_path', 'Modules');
    $modulePath = app_path("{$basePath}/Customer");
    if (File::isDirectory($modulePath)) {
        File::deleteDirectory($modulePath);
    }
});

it('creates service file in correct path', function () {
    $this->artisan('orkestri:make-service', ['module' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Domain/Services/CustomerService.php'))->toBeFile();
});

it('generates service with correct namespace', function () {
    $this->artisan('orkestri:make-service', ['module' => 'Customer'])
        ->assertExitCode(0);

    $content = file_get_contents(app_path('Modules/Customer/Domain/Services/CustomerService.php'));
    expect($content)->toContain('namespace App\Modules\Customer\Domain\Services;');
    expect($content)->toContain('class CustomerService implements ServiceContract');
});

it('respects custom base_path from config', function () {
    config()->set('orkestri.base_path', 'Domains');
    File::makeDirectory(app_path('Domains/Customer/Domain/Services'), 0755, true);

    $this->artisan('orkestri:make-service', ['module' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Domains/Customer/Domain/Services/CustomerService.php'))->toBeFile();
    $content = file_get_contents(app_path('Domains/Customer/Domain/Services/CustomerService.php'));
    expect($content)->toContain('namespace App\Domains\Customer\Domain\Services;');

    File::deleteDirectory(app_path('Domains/Customer'));
    config()->set('orkestri.base_path', 'Modules');
});

it('does not overwrite existing service', function () {
    $servicePath = app_path('Modules/Customer/Domain/Services/CustomerService.php');
    File::put($servicePath, '<?php // existing');

    $this->artisan('orkestri:make-service', ['module' => 'Customer'])
        ->assertExitCode(0);

    expect(file_get_contents($servicePath))->toBe('<?php // existing');
});
