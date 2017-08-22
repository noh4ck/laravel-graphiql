<?php

namespace Graphiql;

use Illuminate\Support\ServiceProvider;
use Graphiql\Console\PublishCommand;

class GraphiqlServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $viewPath = __DIR__.'/../resources/views';
        $this->loadViewsFrom($viewPath, 'graphiql');

        // Publish a config file
        $configPath = __DIR__.'/../config/graphiql.php';
        $this->publishes([
            $configPath => config_path('graphiql.php'),
        ], 'config');

        //Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => config('graphiql.paths.views'),
        ], 'views');

        //Publish assets
        $this->publishes([
            __DIR__.'/../resources/assets' => config('graphiql.paths.assets'),
        ], 'assets');

        //Include routes
        \Route::group(['namespace' => 'Graphiql'], function ($router) {
            $router->get(config('graphiql.routes.ui'), [
                'as' => 'graphiql',
                'middleware' => config('graphiql.routes.middleware', []),
                'uses' => '\Graphiql\Http\Controllers\GraphiqlController@index',
            ]);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/graphiql.php';
        $this->mergeConfigFrom($configPath, 'graphiql');
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishCommand::class
            ]);
        }
    }

}
