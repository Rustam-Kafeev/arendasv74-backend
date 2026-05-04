<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Временное решение CORS для всех доменов (только для Render)
header('Access-Control-Allow-Origin: https://arendasv74.vercel.app');
header('Access-Control-Allow-Origin: https://arendasv74-*.vercel.app');
header('Access-Control-Allow-Origin: https://*.trycloudflare.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Origin: https://arendasv74.vercel.app');
    header('Access-Control-Allow-Origin: https://arendasv74-*.vercel.app');
    header('Access-Control-Allow-Origin: https://*.trycloudflare.com');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(200);
    exit();
}
$app->handleRequest(Request::capture());
