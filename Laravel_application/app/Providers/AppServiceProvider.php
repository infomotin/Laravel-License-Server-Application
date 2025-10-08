<?php

namespace App\Providers;

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
        if ($this->app->runningInConsole()) return;
    try {
        $tokenPath = config('license.token_path');
        if (!file_exists($tokenPath)) {
            abort(403, 'License missing.');
        }
        $token = file_get_contents($tokenPath);
        $svc = app(\App\Services\LicenseService::class);
        $payload = $svc->verifyTokenLocal($token);
        if (!$payload) abort(403, 'Invalid license.');
        // optionally do remote quick validate
    } catch (\Exception $e) {
        abort(403, 'License error.');
    }
    }
}
