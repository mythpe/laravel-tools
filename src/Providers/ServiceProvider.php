<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Myth\LaravelTools\Console\Commands\CheckPermissionNamesCommand;
use Myth\LaravelTools\Console\Commands\Export\ExportAttributesCommand;
use Myth\LaravelTools\Console\Commands\Export\ExportLanguageCommand;
use Myth\LaravelTools\Console\Commands\MakeModelCommand;
use Myth\LaravelTools\Console\Commands\PostmanCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        if (str_starts_with($this->app->version(), '9.')) {
            AboutCommand::add('MyTh Tools', [
                'Version' => '1.0.0',
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/4myth-tools.php', '4myth-tools'
        );

        // Config
        $this->publishes([
            __DIR__.'/../config/4myth-tools.php'   => config_path('4myth-tools.php'),
            __DIR__.'/../config/4myth-getaway.php' => config_path('4myth-getaway.php'),
        ], '4myth-tools-config');

        // Migrations
        $this->publishes([
            __DIR__.'/../Migrations' => $this->app->databasePath('migrations'),
        ], '4myth-tools-migrations');

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', '4myth-tools');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/4myth-tools'),
        ], '4myth-tools-views');

        // Lang
        $this->publishes([
            __DIR__.'/../lang/ar/postman.php' => lang_path('ar/postman.php'),
            __DIR__.'/../lang/en/postman.php' => lang_path('en/postman.php'),
        ], '4myth-tools-lang');

        // Base Model
        $this->publishes([
            __DIR__.'/../Models/BaseModel.php' => app_path("Models/BaseModel.php"),
        ], '4myth-tools-models');

        // Public Assets
        $this->publishes([
            __DIR__.'/../resources/public' => storage_path('app/public/vendor/4myth'),
            __DIR__.'/../resources/vendor' => resource_path('vendor'),
        ], '4myth-tools-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PostmanCommand::class,
                ExportLanguageCommand::class,
                MakeModelCommand::class,
                CheckPermissionNamesCommand::class,
                ExportAttributesCommand::class,
            ]);
        }
    }
}
