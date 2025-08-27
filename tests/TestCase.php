<?php

namespace Nikolaynesov\LaravelCommandAssistant\Tests;

use Illuminate\Contracts\Console\Kernel;
use Nikolaynesov\LaravelCommandAssistant\LaravelCommandAssistantServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelCommandAssistantServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        // Enable the assistant and set the key
        $app['config']->set('command-assistant.enabled', true);
        $app['config']->set('command-assistant.key', 'test-key');

        // Use in-memory sqlite
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
