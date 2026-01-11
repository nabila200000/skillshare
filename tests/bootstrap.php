<?php

// Bootstrap for PHPUnit tests
// Ensure session works in CLI
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Autoload vendor if exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Provide minimal polyfills or helper functions if necessary

