<?php

namespace StianScholtz\TabManager\ServiceProviders;

use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\AuthenticateSession;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use StianScholtz\TabManager\Middleware\CheckForTabId;
use StianScholtz\TabManager\Services\TabManager;

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
                return new TabManager();
            });
        }
    }

    public function packageBooted()
    {
        /**
         * @var Router $router
         */
        $router = $this->app->make(Router::class);

        //Prepend instead of pushing to ensure middleware such as HandleInertiaRequests (in Inertiajs stacks)
        //or others are last the in web group as per their requirements
        $router->prependMiddlewareToGroup('web', CheckForTabId::class);

        //Set priority to ensure the middleware executes after the session have been initialized and authenticated
        $router->middlewarePriority = [
            AuthenticateSession::class,
            CheckForTabId::class,
        ];
    }
}
