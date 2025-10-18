<?php

namespace Modules\SPPassport\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Handles route registration for the SPPassport module.
 *
 * This provider defines and groups all web, admin, and API routes
 * under their appropriate middleware, namespaces, and prefixes.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for this module.
     *
     * @var string
     */
    protected $namespace = 'Modules\SPPassport\Http\Controllers';

    /**
     * Executed before the routes are registered.
     *
     * @param  Router  $router
     * @return void
     */
    public function before(Router $router): void
    {
        // Add model bindings or route patterns here if needed.
    }

    /**
     * Define all routes for this module.
     *
     * @param  Router  $router
     * @return void
     */
    public function map(Router $router): void
    {
        $this->registerWebRoutes();
        $this->registerAdminRoutes();
        $this->registerApiRoutes();
    }

    /**
     * Register the frontend (web) routes for SPPassport.
     *
     * Routes are accessible to authenticated users under the "web" middleware group.
     */
    protected function registerWebRoutes(): void
    {
        $config = [
            'as'         => 'passport.', // Namenspräfix für Routen
            'prefix'     => 'passport',  // URL-Präfix (statt sppassport)
            'namespace'  => $this->namespace . '\Frontend',
            'middleware' => ['web'],
        ];

        Route::group($config, function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/web.php');
        });
    }

    /**
     * Register admin routes for managing SPPassport data.
     *
     * Routes are restricted to users with the "admin" role.
     */
    protected function registerAdminRoutes(): void
    {
        $config = [
            'as'         => 'admin.passport.',
            'prefix'     => 'admin/passport',
            'namespace'  => $this->namespace . '\Admin',
            'middleware' => ['web', 'role:admin'],
        ];

        Route::group($config, function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/admin.php');
        });
    }

    /**
     * Register API routes for SPPassport.
     *
     * Routes are grouped under the "api" middleware and are stateless.
     */
    protected function registerApiRoutes(): void
    {
        $config = [
            'as'         => 'api.passport.',
            'prefix'     => 'api/passport',
            'namespace'  => $this->namespace . '\Api',
            'middleware' => ['api'],
        ];

        Route::group($config, function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/api.php');
        });
    }
}
