<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models;
use Repositories\Places;


class RepoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $repoClassName = 'Repositories\Places\PlacesRepository';
        $modelClassName = 'App\Models\Place';

        $this->app->bind('Repositories\IRepository', function ($app) use($repoClassName, $modelClassName) {
            return new $repoClassName(new $modelClassName);
        });
    }
}
