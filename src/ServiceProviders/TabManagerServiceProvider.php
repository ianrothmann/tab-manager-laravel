<?php

namespace StianScholtz\TabManager\ServiceProviders;

use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use StianScholtz\TabManager\Middleware\TabManagerMiddleware;
use StianScholtz\TabManager\Services\TabManagerService;

class TabManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('tab-manager')
            ->hasConfigFile()
            ->setBasePath(app_path());//Required to have TabManagerServiceProvider namespaced inside the ServiceProviders directory
    }

    public function packageRegistered()
    {
        if (! $this->app->resolved('tab-manager')) {
            $this->app->singleton('tab-manager', function () {
                return new TabManagerService();
            });
        }
    }

    public function packageBooted()
    {
        $this->registerHelpers();
        $this->registerMiddleware();
    }

    private function registerMiddleware()
    {
        /**
         * @var Router $router
         */
        $router = $this->app->make(Router::class);

        $router->pushMiddlewareToGroup('web', TabManagerMiddleware::class);

        //Set priority to ensure the middleware executes after the session have been initialized and authenticated
        $router->middlewarePriority = [
            StartSession::class,
            AuthenticateSession::class,
            TabManagerMiddleware::class,
        ];
    }

    /**
     * Register helpers file
     */
    private function registerHelpers()
    {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '../helpers.php')) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . '../helpers.php';
        }
    }
}
