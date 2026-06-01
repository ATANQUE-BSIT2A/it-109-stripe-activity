<?php

declare(strict_types=1);

final class ApiResponse
{
    private const PROJECT_DEBUG_LOG = __DIR__ . '/../logs/php_debug.log';

    // Send a JSON response back to the browser or frontend script.
    public static function json(array $data, int $statusCode = 200): never
    {
        self::drainBufferedOutput();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Standard error format so every API error looks the same.
    public static function error(string $message, int $statusCode = 400, array $extra = []): never
    {
        self::json(array_merge([
            'success' => false,
            'error' => $message,
        ], $extra), $statusCode);
    }

    private static function drainBufferedOutput(): void
    {
        while (ob_get_level() > 0) {
            $buffer = ob_get_clean();
            if ($buffer !== false && trim($buffer) !== '') {
                self::writeDebugOutput($buffer);
            }
        }
    }

    private static function writeDebugOutput(string $buffer): void
    {
        $logDirectory = dirname(self::PROJECT_DEBUG_LOG);
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        $entry = sprintf(
            "[%s] Buffered API debug output:\n%s\n",
            date('Y-m-d H:i:s'),
            rtrim($buffer)
        );

        error_log($entry, 3, self::PROJECT_DEBUG_LOG);
    }
}
