<?php

// Force LOG_CHANNEL=stderr BEFORE Laravel boots
// This covers the env var approach
putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

// Create required /tmp directories for Vercel's read-only filesystem
$tmpDirs = [
    '/tmp/laravel-storage/logs',
    '/tmp/laravel-storage/framework/views',
    '/tmp/laravel-storage/framework/sessions',
    '/tmp/laravel-storage/framework/cache/data',
    '/tmp/laravel-storage/app/public',
    '/tmp/laravel-storage/app/private',
];
foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Symlink storage/logs → /tmp/laravel-storage/logs so hardcoded paths work
// This is the ONLY way to fix Laravel's hardcoded emergency logger path
$originalLogsDir = dirname(__DIR__) . '/storage/logs';
if (!is_dir($originalLogsDir) && !is_link($originalLogsDir)) {
    @mkdir(dirname($originalLogsDir), 0755, true);
    @symlink('/tmp/laravel-storage/logs', $originalLogsDir);
}

// Forward Vercel requests to public/index.php
require __DIR__ . '/../public/index.php';
