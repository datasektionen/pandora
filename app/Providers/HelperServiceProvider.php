<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('EmailClient', function() {
            return new \App\Helpers\EmailClient;
        });
        $this->app->bind('Planner', function() {
            return new \App\Helpers\Planner;
        });

        parent::boot();
    }
}
