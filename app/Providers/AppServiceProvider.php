<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        // Force HTTPS on Render (production only)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(Login::class, function (Login $event) {
            AuditLog::create([
                'user_id' => $event->user->id,
                'action' => 'user_login',
                'module' => 'Authentication',
                'record_type' => 'User',
                'record_id' => $event->user->id,
                'description' => $event->user->name . ' signed in.',
                'new_values' => ['email' => $event->user->email],
                'ip_address' => request()->ip(),
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) {
                return;
            }

            AuditLog::create([
                'user_id' => $event->user->id,
                'action' => 'user_logout',
                'module' => 'Authentication',
                'record_type' => 'User',
                'record_id' => $event->user->id,
                'description' => $event->user->name . ' signed out.',
                'new_values' => ['email' => $event->user->email],
                'ip_address' => request()->ip(),
            ]);
        });
    }
}