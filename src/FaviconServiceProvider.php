<?php

namespace BeyondCode\LaravelFavicon;

use BeyondCode\LaravelFavicon\Generators\FaviconGenerator;
use BeyondCode\LaravelFavicon\Http\Controllers\FaviconController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FaviconServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/favicon.php' => config_path('favicon.php'),
            ], 'config');
        }

        $this->registerRoutes();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/favicon.php', 'favicon');

        $this->app->singleton(Favicon::class, function () {
            return new Favicon(config('favicon'));
        });

        $this->app->bind(FaviconGenerator::class, function () {
            return app(config('favicon.generator'));
        });
    }

    protected function registerRoutes()
    {
        Route::get(config('favicon.url_prefix').'/{icon}', FaviconController::class)
            ->where('icon', '.*');
    }
}
