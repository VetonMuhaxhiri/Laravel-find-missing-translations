<?php

namespace VetonMuhaxhiri\Laravelfindmissingtranslations\Providers;

use Illuminate\Support\ServiceProvider;
use VetonMuhaxhiri\Laravelfindmissingtranslations\Commands\FindMissingTranslations;

class FindMissingTranslationsProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FindMissingTranslations::class
            ]);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
