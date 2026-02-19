<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $basePath = config('orkestri.base_path', 'Modules');
    $modulePath = app_path("{$basePath}/Customer");
    if (File::isDirectory($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    File::makeDirectory(app_path("{$basePath}/Customer/Domain/Models"), 0755, true);
});

afterEach(function () {
    $basePath = config('orkestri.base_path', 'Modules');
    $modulePath = app_path("{$basePath}/Customer");
    if (File::isDirectory($modulePath)) {
        File::deleteDirectory($modulePath);
    }
});

it('creates model file in correct path', function () {
    $this->artisan('orkestri:make-model', ['module' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Modules/Customer/Domain/Models/Customer.php'))->toBeFile();
});

it('generates model with correct namespace', function () {
    $this->artisan('orkestri:make-model', ['module' => 'Customer'])
        ->assertExitCode(0);

    $content = file_get_contents(app_path('Modules/Customer/Domain/Models/Customer.php'));
    expect($content)->toContain('namespace App\Modules\Customer\Domain\Models;');
    expect($content)->toContain('class Customer extends Model');
});

it('generates model with correct table name', function () {
    $this->artisan('orkestri:make-model', ['module' => 'Customer'])
        ->assertExitCode(0);

    $content = file_get_contents(app_path('Modules/Customer/Domain/Models/Customer.php'));
    expect($content)->toContain("protected \$table = 'customers';");
});

it('respects custom base_path from config', function () {
    config()->set('orkestri.base_path', 'Domains');
    File::makeDirectory(app_path('Domains/Customer/Domain/Models'), 0755, true);

    $this->artisan('orkestri:make-model', ['module' => 'Customer'])
        ->assertExitCode(0);

    expect(app_path('Domains/Customer/Domain/Models/Customer.php'))->toBeFile();
    $content = file_get_contents(app_path('Domains/Customer/Domain/Models/Customer.php'));
    expect($content)->toContain('namespace App\Domains\Customer\Domain\Models;');

    File::deleteDirectory(app_path('Domains/Customer'));
    config()->set('orkestri.base_path', 'Modules');
});

it('does not overwrite existing model', function () {
    $modelPath = app_path('Modules/Customer/Domain/Models/Customer.php');
    File::put($modelPath, '<?php // existing');

    $this->artisan('orkestri:make-model', ['module' => 'Customer'])
        ->assertExitCode(0);

    expect(file_get_contents($modelPath))->toBe('<?php // existing');
});
