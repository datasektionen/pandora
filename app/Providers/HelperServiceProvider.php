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

        parent::boot();
    }
}
