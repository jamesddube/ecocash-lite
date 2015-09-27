<?php

namespace Pay4App\EcoCashLite;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Pay4App\GatewayConfig;
use Pay4App\Services\CheckoutHandler;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupRoutes($this->app->router);
        $this->loadViewsFrom(__DIR__.'/resources/views', 'ecocashlite');
        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/pay4app/ecocashlite'),
        ]);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        require __DIR__.'/Http/routes.php';

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}