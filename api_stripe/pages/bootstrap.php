<?php

declare(strict_types=1);

// Load all shared classes needed by the pages in this project.
require_once dirname(__DIR__) . '/classes/Config.php';
require_once dirname(__DIR__) . '/classes/ApiResponse.php';
require_once dirname(__DIR__) . '/classes/Request.php';
require_once dirname(__DIR__) . '/classes/StripeClient.php';

// Buffer accidental debug output so API JSON responses are not corrupted.
ob_start();

// Convert uncaught PHP errors into JSON so API pages fail consistently.
set_exception_handler(static function (Throwable $exception): void {
    ApiResponse::error($exception->getMessage(), 500);
});
