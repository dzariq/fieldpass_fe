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
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Generate https:// URLs in deployed environments (avoids mixed-content XHR to http:// on an HTTPS site).
        // Local / testing: never force HTTPS so http://127.0.0.1 and php artisan serve work without TLS.
        if (! $this->app->environment('local', 'testing')) {
            $appUrl = (string) config('app.url', '');
            if (
                $this->app->environment('production')
                || filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN)
                || env('REDIRECT_HTTPS')
                || str_starts_with($appUrl, 'https://')
            ) {
                URL::forceScheme('https');
            }
        }

        Paginator::defaultView('vendor.pagination.bootstrap-4');
    }
}
