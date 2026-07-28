<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Pre-create all required /tmp directories BEFORE Application boots
// This must happen here (not in api/index.php) because bootstrap/app.php
// is the last interception point before the Application object is created
if (is_writable('/tmp')) {
    foreach ([
        '/tmp/laravel/storage/logs',
        '/tmp/laravel/storage/framework/views',
        '/tmp/laravel/storage/framework/sessions',
        '/tmp/laravel/storage/framework/cache/data',
        '/tmp/laravel/storage/app/public',
        '/tmp/laravel/storage/app/private',
    ] as $dir) {
        is_dir($dir) || mkdir($dir, 0755, true);
    }
}

// Build the Application with overridden storagePath
// useStoragePath() MUST be called before any service providers run.
// Calling it right after create() is safe because providers are lazy-booted.
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

// Override storage path so EVERY internal Laravel call (including the
// hardcoded emergency logger in LogManager::createEmergencyLogger())
// resolves to /tmp instead of the read-only /var/task/user/storage
if (is_writable('/tmp')) {
    $app->useStoragePath('/tmp/laravel/storage');
}

return $app;
