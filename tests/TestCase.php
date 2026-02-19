<?php

namespace Tests;

use LucasKaiut\Orkestri\OrkestriServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            OrkestriServiceProvider::class,
        ];
    }
}
