<?php

// Force LOG_CHANNEL=stderr BEFORE Laravel boots (belt-and-suspenders approach)
putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

// Forward Vercel requests to public/index.php
// Storage path override and /tmp dir creation is handled in bootstrap/app.php
require __DIR__ . '/../public/index.php';
