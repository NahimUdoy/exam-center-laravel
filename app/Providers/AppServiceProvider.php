<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $localHttp = ! $this->app->runningInConsole()
            && in_array(request()->getHost(), ['127.0.0.1', 'localhost', '::1'], true)
            && ! request()->isSecure();

        if ($this->app->environment('production') && ! $localHttp) {
            URL::forceScheme('https');
        }
    }
}
