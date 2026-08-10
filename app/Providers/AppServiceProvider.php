<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(
            ['components.layout.header', 'components.layout.footer'],
            NavigationComposer::class
        );

        // Pagination links are rendered with the template's own markup.
        Paginator::defaultView('vendor.pagination.globetrek');
        Paginator::defaultSimpleView('vendor.pagination.globetrek');
    }
}
