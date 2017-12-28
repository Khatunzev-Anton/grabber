<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models;
use Services\Grab;
use Services\Grab\Gelbeseiten as Gelbeseiten;

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
        $grabServiceName = 'Services\Grab\Gelbeseiten\GelbeseitenDeService';
        if($this->app->request->is('*/googlecheck')){
            $grabServiceName = 'Services\Grab\Google\GoogleComService';
        }elseif($this->app->request->is('*/grabgoogle')){
            $grabServiceName = 'Services\Grab\GooglePlaces\GelbeseitenDeService';
        }
        $this->app->bind('Services\Grab\IGrabService', $grabServiceName);
    }
}
