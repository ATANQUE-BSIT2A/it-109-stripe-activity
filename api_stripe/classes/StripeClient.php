<?php

declare(strict_types=1);

final class StripeClient
{
    private string $secretKey;
    private string $apiVersion;
    private string $baseUrl = 'https://api.stripe.com';

    public function __construct(?string $secretKey = null, ?string $apiVersion = null)
    {
        // Use the configured Stripe key unless one is passed in manually.
        $this->secretKey = $secretKey ?? Config::getStripeSecretKey();
        $this->apiVersion = $apiVersion ?? Config::getStripeApiVersion();
    }

    public function get(string $path, array $params = []): array
    {
        return $this->request('GET', $path, $params);
    }

    public function post(string $path, array $params = []): array
    {
        return $this->request('POST', $path, $params);
    }

    public function delete(string $path, array $params = []): array
    {
        return $this->request('DELETE', $path, $params);
    }

    public function request(string $method, string $path, array $params = [], array $headers = []): array
    {
        // USE var_dump() for debugging
        $method = strtoupper($method);
        $path = '/' . ltrim($path, '/');
        $url = $this->baseUrl . $path;

        var_dump($url);

        // GET requests send data in the URL query string.
        if ($method === 'GET' && $params !== []) {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init($url);

        $requestHeaders = array_merge([
            'Authorization: Bearer ' . $this->secretKey,
            'Stripe-Version: ' . $this->apiVersion,
            'Accept: application/json',
        ], $headers);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        // POST and DELETE requests send data in Stripe's form-encoded format.
        if ($method !== 'GET' && $params !== []) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        // send to Stripe
        $raw = curl_exec($curl);

        if ($raw === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('Stripe request failed: ' . $error);
        }

        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Stripe responds with JSON, so decode it before returning it.
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Stripe returned a non-JSON response.');
        }

        return [
            'status_code' => $statusCode,
            // "ok" is a quick success flag for HTTP 2xx responses.
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $decoded,
        ];
    }
}
