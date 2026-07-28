<?php

// Force critical environment variables BEFORE Laravel boots
// This ensures logging works even if config cache is stale or missing
putenv('LOG_CHANNEL=stderr');
putenv('APP_DEBUG=false');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

// Ensure /tmp storage directories exist on Vercel's read-only filesystem
foreach ([
    '/tmp/views',
    '/tmp/sessions',
    '/tmp/cache',
    '/tmp/storage/private',
    '/tmp/storage/public',
    '/tmp/storage/public/attachments',
    '/tmp/storage/logs',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Override storage path to /tmp to avoid read-only filesystem errors
// This ensures any code that resolves storage_path() gets a writable path
if (!defined('LARAVEL_STORAGE_PATH')) {
    define('LARAVEL_STORAGE_PATH', '/tmp/storage');
}

// Forward Vercel requests to public/index.php
require __DIR__ . '/../public/index.php';
