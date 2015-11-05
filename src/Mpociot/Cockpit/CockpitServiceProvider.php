<?php namespace Mpociot\Cockpit;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Khill\Lavacharts\Laravel\LavachartsFacade;
use Khill\Lavacharts\Laravel\LavachartsServiceProvider;

/**
 * Class CockpitServiceProvider
 * @package Mpociot\Cockpit
 */
class CockpitServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishMigration();
        $this->publishAssets();
        $this->publishViews();
        $this->registerRoutes();
    }

    /**
     * Publish Cockpit's configuration
     */
    protected function publishConfig()
    {
        // Publish config files
        $this->publishes( [
            __DIR__ . '/../../config/config.php' => config_path( 'cockpit.php' ),
        ] );
    }

    /**
     * Publish Cockpit's migration
     */
    protected function publishMigration()
    {
        $published_migration = glob( database_path( '/migrations/*_cockpit_setup_table.php' ) );
        if( count( $published_migration ) === 0 )
        {
            $this->publishes( [
                __DIR__ . '/../../database/2015_11_02_000000_cockpit_setup_table.php' => database_path( '/migrations/' . date( 'Y_m_d_His' ) . '_cockpit_setup_table.php' ),
            ], 'migrations' );
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the Lavacharts service provider
        $this->app->register( LavachartsServiceProvider::class );
        AliasLoader::getInstance()->alias('Lava', LavachartsFacade::class );
    }

    /**
     * Register the predefined routes
     */
    private function registerRoutes()
    {
        include __DIR__.'/../../routes.php';
    }

    /**
     * Publish JS files
     */
    protected function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../../public/css/cockpit.css' => public_path('css/cockpit/cockpit.css'),
            __DIR__.'/../../public/js/app.js' => public_path('js/cockpit/app.js'),
            __DIR__.'/../../public/js/filter.js' => public_path('js/cockpit/filter.js'),
            __DIR__.'/../../public/js/global_filter.js' => public_path('js/cockpit/global_filter.js'),
            __DIR__.'/../../public/js/widget.js' => public_path('js/cockpit/widget.js'),
            // Dummy metric to get started
            __DIR__.'/../../Metrics/Users.php' => app_path('Cockpit/Metrics/Users.php'),
        ]);
    }

    /**
     * Publish the views
     */
    protected function publishViews()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'cockpit');
        $this->publishes([
            __DIR__.'/../../resources/views' => base_path('resources/views/vendor/cockpit'),
        ], 'cockpit');
    }

}