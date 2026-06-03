<?php

declare(strict_types=1);

// ============================================================================
// STRIPECLIENT CLASS - Talks to Stripe's API
// Sends requests to Stripe using cURL and returns the responses
// ============================================================================
final class StripeClient
{
    private string $secretKey;
    private string $apiVersion;
    private string $baseUrl = 'https://api.stripe.com';

    // Constructor: sets up our secret key and API version
    public function __construct(?string $secretKey = null, ?string $apiVersion = null)
    {
        // Use our configured secret key unless we pass one manually
        $this->secretKey = $secretKey ?? Config::getStripeSecretKey();
        $this->apiVersion = $apiVersion ?? Config::getStripeApiVersion();
    }

    // Sends a GET request to Stripe
    public function get(string $path, array $params = []): array
    {
        return $this->request('GET', $path, $params);
    }

    // Sends a POST request to Stripe (most used - creates customers, payments, etc.)
    public function post(string $path, array $params = []): array
    {
        return $this->request('POST', $path, $params);
    }

    // Sends a DELETE request to Stripe
    public function delete(string $path, array $params = []): array
    {
        return $this->request('DELETE', $path, $params);
    }

    // The main function that actually sends the request to Stripe using cURL
    public function request(string $method, string $path, array $params = [], array $headers = []): array
    {
        $method = strtoupper($method);
        $path = '/' . ltrim($path, '/');
        $url = $this->baseUrl . $path;

        // Debug: uncomment this to see the full URL we're calling
        // var_dump($url);

        // For GET requests: add parameters to the URL
        if ($method === 'GET' && $params !== []) {
            $url .= '?' . http_build_query($params);
        }

        // Initialize cURL
        $curl = curl_init($url);

        // Set up our request headers (includes our secret key for authentication)
        $requestHeaders = array_merge([
            'Authorization: Bearer ' . $this->secretKey,
            'Stripe-Version: ' . $this->apiVersion,
            'Accept: application/json',
        ], $headers);

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,    // Return response as a string
            CURLOPT_CUSTOMREQUEST => $method,  // HTTP method (GET/POST/DELETE)
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_TIMEOUT => 30,             // Time out after 30 seconds
            CURLOPT_CONNECTTIMEOUT => 10,      // Time out connecting after 10 seconds
        ]);

        // For POST/DELETE requests: send data in the request body
        if ($method !== 'GET' && $params !== []) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        // Send the request to Stripe!
        $raw = curl_exec($curl);

        // If the request failed, throw an error
        if ($raw === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('Stripe request failed: ' . $error);
        }

        // Get the HTTP status code from the response
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Stripe returns JSON, so decode it into an array
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Stripe returned a non-JSON response.');
        }

        // Return a nice array with status code, success flag, and data
        return [
            'status_code' => $statusCode,
            'ok' => $statusCode >= 200 && $statusCode < 300, // True if 200-299 status
            'data' => $decoded,
        ];
    }
}
