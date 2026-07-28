<?php

// Ensure /tmp storage directories exist on Vercel's read-only filesystem
foreach ([
    '/tmp/views',
    '/tmp/sessions',
    '/tmp/cache',
    '/tmp/storage/private',
    '/tmp/storage/public',
    '/tmp/storage/public/attachments',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Forward Vercel requests to public/index.php
require __DIR__ . '/../public/index.php';
