<?php

namespace Nikolaynesov\LaravelCommandAssistant;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class LaravelCommandAssistantServiceProvider  extends ServiceProvider
{

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/command-assistant.php',
            'command-assistant'
        );

        $this->publishes([
            // Config
            __DIR__ . '/../config/command-assistant.php' => config_path('command-assistant.php'),

            // Plugin files
            __DIR__ . '/../public/.well-known/ai-plugin.command-assistant.json' =>
                public_path('vendor/laravel-command-assistant/.well-known/ai-plugin.json'),

            __DIR__ . '/../public/.well-known/openapi.command-assistant.yaml' =>
                public_path('vendor/laravel-command-assistant/.well-known/openapi.yaml'),

        ], 'laravel-command-assistant');

        // Register middleware alias
        $this->app->booted(function () {
            $router = $this->app->make(Router::class);
            $router->aliasMiddleware('verify.laravel-command-assistant.key', VerifyKeyMiddleware::class);
        });

    }

}