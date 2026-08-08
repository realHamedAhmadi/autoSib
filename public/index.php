<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * Suppress specific errors for PHP 8.5 and above
 */
if (version_compare(PHP_VERSION, '8.5.0', '>=')) {
    // Disable displaying errors to the screen
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');

    // Report only fatal errors, ignoring Warnings, Notices, and Deprecated messages
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
