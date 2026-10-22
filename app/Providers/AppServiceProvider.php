<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('admin-login', function (Request $request): array {
            $email = Str::lower(trim((string) $request->input('email')));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by("email:{$email}|ip:{$ip}"),
                Limit::perMinute(20)->by("ip:{$ip}"),
            ];
        });

        RateLimiter::for('public-booking-catalog', fn (Request $request): Limit => Limit::perMinute(120)->by('ip:'.(string) $request->ip()));

        RateLimiter::for('public-booking-professionals', fn (Request $request): Limit => Limit::perMinute(60)->by('ip:'.(string) $request->ip()));

        RateLimiter::for('public-booking-availability', fn (Request $request): Limit => Limit::perMinute(30)->by('ip:'.(string) $request->ip()));

        RateLimiter::for('public-booking-appointments', fn (Request $request): Limit => Limit::perMinute(5)->by('ip:'.(string) $request->ip()));
    }
}
