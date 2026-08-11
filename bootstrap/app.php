<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * In production the app sits behind a load balancer that terminates
         * TLS and forwards plain http, so without this Laravel decides every
         * request is insecure and writes http:// into every generated URL —
         * form actions and redirects included, which the browser then blocks
         * as mixed content on an https page.
         *
         * Trusting every proxy is safe only because nothing reaches the
         * container except through that balancer. If the app is ever exposed
         * directly, this has to become the balancer's address instead: a
         * client could otherwise forge X-Forwarded-For and spoof its own IP.
         */
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'staff' => EnsureUserIsStaff::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
