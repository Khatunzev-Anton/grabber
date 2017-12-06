<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models;
use Services\Grab;
use Services\Grab\Gelbeseiten;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Services\Grab\IGrabService', 'Services\Grab\Gelbeseiten\GelbeseitenDeService');
    }
}
