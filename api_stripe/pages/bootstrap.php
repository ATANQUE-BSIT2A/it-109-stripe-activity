<?php

declare(strict_types=1);

// ============================================================================
// BOOTSTRAP.PHP - Sets up our project
// Loads all our classes, turns on output buffering, and handles errors
// ============================================================================

// Load all our core classes (so we don't have to require them one by one)
require_once dirname(__DIR__) . '/classes/Config.php';
require_once dirname(__DIR__) . '/classes/ApiResponse.php';
require_once dirname(__DIR__) . '/classes/Request.php';
require_once dirname(__DIR__) . '/classes/StripeClient.php';

// Turn on output buffering: catches accidental extra output (like stray echos)
// This keeps our JSON responses clean!
ob_start();

// If there's an uncaught error, send it as a JSON response instead of a messy error page
set_exception_handler(static function (Throwable $exception): void {
    ApiResponse::error($exception->getMessage(), 500);
});
