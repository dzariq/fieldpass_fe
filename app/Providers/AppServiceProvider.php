<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Generate https:// URLs when the app is configured for HTTPS (avoids mixed-content XHR to http:// on an HTTPS site).
        $appUrl = (string) config('app.url', '');
        if (
            $this->app->environment('production')
            || env('REDIRECT_HTTPS')
            || str_starts_with($appUrl, 'https://')
        ) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('vendor.pagination.bootstrap-4');
    }
}
