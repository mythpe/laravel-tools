<?php

namespace Myth\LaravelTools\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Myth\LaravelTools\Console\Commands\JsLangCommand;
use Myth\LaravelTools\Console\Commands\MakeModelCommand;
use Myth\LaravelTools\Console\Commands\PostmanCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (str_starts_with($this->app->version(), '9.')) {
            \Illuminate\Foundation\Console\AboutCommand::add('MyTh Tools', [
                'Version' => '1.0.0',
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/4myth-tools.php', '4myth-tools'
        );
        $this->publishes([
            __DIR__.'/../config/4myth-tools.php' => config_path('4myth-tools.php'),
        ], '4myth-tools-config');

        // Translations
        // $this->loadTranslationsFrom(__DIR__.'/../lang', '4myth-tools');
        // $this->publishes([
        //     __DIR__.'/../lang' => $this->app->langPath('vendor/4myth-tools'),
        // ]);

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', '4myth-tools');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/4myth-tools'),
        ]);

        // Base Model
        $this->publishes([
            __DIR__.'/../Models/BaseModel.php' => app_path("Models/BaseModel.php"),
        ], '4myth-tools-models');

        // Public Assets
        $this->publishes([
            __DIR__.'/../resources/public' => storage_path('app/public/vendor/4myth'),
            __DIR__.'/../resources/vendor' => resource_path(),
        ], 'public');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PostmanCommand::class,
                JsLangCommand::class,
                MakeModelCommand::class,
            ]);
        }
    }
}
