<?php

declare(strict_types=1);

// ============================================================================
// APIRESPONSE CLASS - Sends JSON responses back to the frontend
// Makes sure all our success/error messages look consistent
// ============================================================================
final class ApiResponse
{
    // Path to our debug log file (stores accidental extra output)
    private const PROJECT_DEBUG_LOG = __DIR__ . '/../logs/php_debug.log';

    // Sends a JSON response back to the browser/frontend
    public static function json(array $data, int $statusCode = 200): never
    {
        // Clear any accidental extra output so our JSON stays clean
        self::drainBufferedOutput();
        
        // Set HTTP status code (like 200 = OK, 422 = Bad Request)
        http_response_code($statusCode);
        
        // Tell the browser we're sending JSON
        header('Content-Type: application/json; charset=utf-8');
        
        // Output our data as nicely formatted JSON
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Stop the script here (we're done sending the response)
        exit;
    }

    // Sends a standard JSON error message
    public static function error(string $message, int $statusCode = 400, array $extra = []): never
    {
        // Combine our error message with any extra data, then send as JSON
        self::json(array_merge([
            'success' => false,
            'error' => $message,
        ], $extra), $statusCode);
    }

    // Clears any accidental extra output (like stray echos)
    private static function drainBufferedOutput(): void
    {
        while (ob_get_level() > 0) {
            $buffer = ob_get_clean();
            if ($buffer !== false && trim($buffer) !== '') {
                // If there was extra output, write it to our debug log
                self::writeDebugOutput($buffer);
            }
        }
    }

    // Writes accidental extra output to a log file for debugging
    private static function writeDebugOutput(string $buffer): void
    {
        $logDirectory = dirname(self::PROJECT_DEBUG_LOG);
        
        // Create the logs directory if it doesn't exist
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        // Format the log entry with a timestamp
        $entry = sprintf(
            "[%s] Buffered API debug output:\n%s\n",
            date('Y-m-d H:i:s'),
            rtrim($buffer)
        );

        // Write the entry to our debug log file
        error_log($entry, 3, self::PROJECT_DEBUG_LOG);
    }
}
