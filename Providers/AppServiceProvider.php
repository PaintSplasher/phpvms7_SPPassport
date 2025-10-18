<?php

namespace Modules\SPPassport\Providers;

use App\Contracts\Modules\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The provider should not be deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Reference to the module service.
     *
     * @var \App\Services\ModuleService|null
     */
    protected ?\App\Services\ModuleService $moduleSvc = null;

    // Boot the module and register all resources.
    public function boot(): void
    {
        $this->moduleSvc = app('App\Services\ModuleService');

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerAssets();
        $this->registerLinks();
        $this->publishAssets();

        // Make CSS files automatically available to all sppassport views
        \View::composer('sppassport::*', function ($view) {
            $view->with('sppassport_css', asset('sppassport/style.css'));
            $view->with('leaflet_css', asset('sppassport/leaflet.css'));
        });
    }

    // Register bindings in the container.
    public function register(): void
    {
        // Add bindings if necessary
    }

    // Register frontend and admin navigation links.
    public function registerLinks(): void
    {
        $this->moduleSvc->addFrontendLink('Passport', '/passport', 'bi bi-passport', $logged_in = true);
        // $this->moduleSvc->addAdminLink('SPPassport', '/admin/passport', 'pe-7s-world');
    }

    // Register and merge the module configuration.
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('sppassport.php'),
        ], 'sppassport-config');

        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'sppassport');
    }

    // Register module views and allow theme overrides.
    protected function registerViews(): void
    {
        $viewPath   = resource_path('views/modules/sppassport');
        $sourcePath = __DIR__ . '/../Resources/views';

        // Allow theme overrides for the module views
        $themePaths = array_map(function ($path) {
            return str_replace('default', setting('general.theme'), $path) . '/modules/sppassport';
        }, config('view.paths'));

        $this->loadViewsFrom(array_merge($themePaths, [$sourcePath]), 'sppassport');
        $this->publishes([$sourcePath => $viewPath], 'sppassport-views');
    }

    // Load module translations.
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/sppassport');

        $this->loadTranslationsFrom(
            is_dir($langPath) ? $langPath : __DIR__ . '/../Resources/lang',
            'sppassport'
        );
    }

    // Publish static module assets (CSS, images, JSON, flags).
    protected function registerAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../Resources/assets/style.css'          => public_path('sppassport/style.css'),
            __DIR__ . '/../Resources/assets/leaflet.css'        => public_path('sppassport/leaflet.css'),
            __DIR__ . '/../Resources/assets/airport_marker.png' => public_path('sppassport/airport_marker.png'),
            __DIR__ . '/../Resources/assets/custom.geo.json'    => public_path('sppassport/custom.geo.json'),
            __DIR__ . '/../Resources/assets/flags'              => public_path('sppassport/flags'),
        ], 'sppassport-assets');
    }

    // Automatically publish assets if they are missing.
    protected function publishAssets(): void
    {
        $assets = [
            'style.css',
            'leaflet.css',
            'airport_marker.png',
            'custom.geo.json',
            'flags', // directory
        ];

        foreach ($assets as $asset) {
            $sourcePath = __DIR__ . '/../Resources/assets/' . $asset;
            $targetPath = public_path('sppassport/' . $asset);

            if (!file_exists($targetPath)) {
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $targetPath);
                } elseif (file_exists($sourcePath)) {
                    if (!is_dir(dirname($targetPath))) {
                        mkdir(dirname($targetPath), 0755, true);
                    }
                    $this->safeCopy($sourcePath, $targetPath);
                }
            }
        }
    }

    // Recursively copy a directory and its contents.
    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $directory = opendir($source);
        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $src = $source . '/' . $file;
            $dest = $destination . '/' . $file;

            if (is_dir($src)) {
                $this->copyDirectory($src, $dest);
            } else {
                copy($src, $dest);
            }
        }
        closedir($directory);
    }

    // Safely copy a file using stream operations.
    private function safeCopy(string $source, string $destination): void
    {
        $sourceHandle = fopen($source, 'rb');
        $destinationHandle = fopen($destination, 'wb');

        if ($sourceHandle && $destinationHandle) {
            stream_copy_to_stream($sourceHandle, $destinationHandle);
        }

        if ($sourceHandle) {
            fclose($sourceHandle);
        }

        if ($destinationHandle) {
            fclose($destinationHandle);
        }
    }
}
