<?php require __DIR__ . '/config/init.php';
header('Content-Type: text/plain; charset=utf-8');
echo "ENV=" . env('APP_ENV', '(none)') . PHP_EOL;
echo "DB="  . env('DB_NAME', '(none)') . PHP_EOL;
echo "SITE=" . (env('TURNSTILE_SITE') ? 'set' : 'missing') . PHP_EOL;
