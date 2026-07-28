<?php

// Belt-and-suspenders: also force LOG_CHANNEL via putenv before index.php loads
putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

require __DIR__ . '/../public/index.php';
