<?php

namespace JacquesTredoux\VueLoginFlow;

use Illuminate\Support\ServiceProvider;

class VueLoginFlowServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vue-login-flow.php',
            'vue-login-flow'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/vue-login-flow.php' => config_path('vue-login-flow.php'),
        ], 'vue-login-flow-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
            ]);
        }
    }
}

