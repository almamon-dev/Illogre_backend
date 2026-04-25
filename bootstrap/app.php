<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IsOwner;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\IsSupportAgent;
use App\Http\Middleware\IsSupportManager;
use App\Http\Middleware\UpdateUserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            UpdateUserActivity::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'owner' => IsOwner::class,
            'subscribed' => CheckSubscription::class,
            'support_manager' => IsSupportManager::class,
            'support_agent' => IsSupportAgent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
