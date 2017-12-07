<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models;
use Repositories\Places;
use Repositories\Lawers;


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
        $lookupRepoClassName = 'Repositories\Places\PlacesRepository';
        $lookupModelClassName = 'App\Models\Place';

        $this->app->bind('Repositories\ILookupRepository', function ($app) use($lookupRepoClassName, $lookupModelClassName) {
            return new $lookupRepoClassName(new $lookupModelClassName);
        });

        $RepoClassName = 'Repositories\Lawers\LawersRepository';
        $ModelClassName = 'App\Models\Lawer';

        $this->app->bind('Repositories\IRepository', function ($app) use($RepoClassName, $ModelClassName) {
            return new $RepoClassName(new $ModelClassName);
        });
    }
}
