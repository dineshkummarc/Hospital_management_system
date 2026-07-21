<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        // Inject unread notification count into every authenticated layout
        View::composer('layouts.app', function ($view) {
            $unreadNotifications = 0;
            $view->with('unreadNotifications', $unreadNotifications);
        });
    }
}
