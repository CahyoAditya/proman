<?php

// Create writable directories in /tmp for Vercel's read-only filesystem
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

// Replace the read-only storage/logs with a writable one via environment override
// LOG_CHANNEL=stderr is set in vercel.json, so normal logs go to stderr.
// For the hardcoded emergency logger path, we ensure storage/logs is writable
// by overriding the storage path binding after the app is created in public/index.php
// This is handled automatically by the APP_CONFIG_CACHE path in vercel.json.

require __DIR__ . '/../public/index.php';
