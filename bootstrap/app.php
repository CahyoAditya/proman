<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// On Vercel, the filesystem is read-only except /tmp.
// We override the storage path to /tmp/storage so that:
//   1. Laravel's hardcoded emergency logger (vendor/monolog) writes to /tmp
//   2. Session files (if file driver used) go to /tmp
//   3. Compiled views go to /tmp
// This is the ONLY reliable fix — changing env vars alone won't override
// the hardcoded storage_path() calls inside vendor code.
$storagePath = is_writable('/tmp') ? '/tmp/laravel-storage' : dirname(__DIR__) . '/storage';

// Create required subdirectories in /tmp
foreach ([
    $storagePath . '/logs',
    $storagePath . '/framework/views',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/cache',
    $storagePath . '/app/public',
    $storagePath . '/app/private',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

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
    })->create();

// Override storage path AFTER app is created so all internal references resolve to /tmp
if (is_writable('/tmp')) {
    $app->useStoragePath($storagePath);
}

return $app;
