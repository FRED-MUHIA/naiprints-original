<?php

namespace App\Services;

use App\Models\InvoiceSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MpesaService
{
    public function __construct(private ?InvoiceSetting $settings = null)
    {
    }

    public function config(): array
    {
        $settings = $this->settings ?: InvoiceSetting::first();
        $raw = is_array($settings?->mpesa_config) ? $settings->mpesa_config : [];
        $envOverrides = config('services.mpesa', []);

        if (is_array($envOverrides)) {
            foreach ($envOverrides as $key => $value) {
                $value = $this->normalizeConfigValue($value);
                if ($value === null || $value === '') {
                    continue;
                }
                $raw[$key] = $value;
            }
        }

        $normalized = [];
        foreach ($raw as $key => $value) {
            $value = $this->normalizeConfigValue($value);
            $normalized[$key] = $value;
        }

        if (!empty($normalized['environment']) && is_string($normalized['environment'])) {
            $normalized['environment'] = strtolower($normalized['environment']);
        }

        foreach (['shortcode', 'party_b', 'till_number'] as $numericKey) {
            if (!empty($normalized[$numericKey]) && is_string($normalized[$numericKey])) {
                $normalized[$numericKey] = preg_replace('/\D+/', '', $normalized[$numericKey]);
            }
        }

        return $normalized;
    }

    private function normalizeConfigValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $placeholder = strtolower($value);
        if (in_array($placeholder, ['undefined', 'null', 'nil', 'none', 'n/a', 'na'], true)) {
            return '';
        }

        return $value;
    }

    private function tokenCacheKey(): string
    {
        $config = $this->config();
        return 'mpesa_token_' . ($config['environment'] ?? 'sandbox');
    }

    public function isConfigured(): bool
    {
        $config = $this->config();

        return !empty($config['consumer_key'])
            && !empty($config['consumer_secret'])
            && !empty($config['shortcode']);
    }

    public function baseUrl(): string
    {
        $environment = $this->config()['environment'] ?? 'sandbox';

        return $environment === 'live'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function accessToken(): string
    {
        $config = $this->config();
        $key = $config['consumer_key'] ?? null;
        $secret = $config['consumer_secret'] ?? null;

        if (!$key || !$secret) {
            throw new \RuntimeException('M-Pesa consumer key/secret are missing.');
        }

        $cacheKey = $this->tokenCacheKey();

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($key, $secret) {
            $response = Http::timeout(30)
                ->withBasicAuth($key, $secret)
                ->get($this->baseUrl() . '/oauth/v1/generate', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();

            $token = $response['access_token'] ?? null;
            if (!$token) {
                throw new \RuntimeException('Failed to obtain M-Pesa access token.');
            }

            return $token;
        });
    }

    public function normalizePhone(string $phone): string
    {
        $value = trim($phone);
        if ($value === '') {
            return '';
        }

        if (preg_match('/(?:\+|00)?254\s*[-\s()]?0?([17]\d{8})\b/u', $value, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/\b0([17]\d{8})\b/u', $value, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/\b([17]\d{8})\b/u', $value, $matches)) {
            return '254' . $matches[1];
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return '';
        }

        if (Str::startsWith($digits, '00254')) {
            $digits = substr($digits, 2);
        }

        if (preg_match('/^2540([17]\d{8})$/', $digits, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/^0([17]\d{8})$/', $digits, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/^([17]\d{8})$/', $digits, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/^254[17]\d{8}$/', $digits)) {
            return $digits;
        }

        return $digits;
    }

    public function validatePhone(string $phone): string
    {
        $normalized = $this->normalizePhone($phone);
        if (!preg_match('/^254[17]\d{8}$/', $normalized)) {
            throw new RuntimeException('Invalid M-Pesa phone format. Use 07XXXXXXXX/01XXXXXXXX, +2547XXXXXXXX/+2541XXXXXXXX, or 2547XXXXXXXX/2541XXXXXXXX.');
        }

        return $normalized;
    }

    public function normalizeAmount(float|int|string $amount): int
    {
        $value = (float) $amount;
        $normalized = (int) round($value, 0, PHP_ROUND_HALF_UP);

        return max(1, $normalized);
    }

    public function sanitizeCallbackUrl(string $url): string
    {
        $callbackUrl = trim($url);
        if ($callbackUrl === '') {
            throw new RuntimeException('M-Pesa callback URL is missing.');
        }

        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('M-Pesa callback URL is invalid.');
        }

        $parts = parse_url($callbackUrl);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $isLive = ($this->config()['environment'] ?? 'sandbox') === 'live';

        if ($isLive && $scheme === 'http') {
            $callbackUrl = preg_replace('/^http:/i', 'https:', $callbackUrl) ?? $callbackUrl;
            $parts = parse_url($callbackUrl);
            $scheme = strtolower((string) ($parts['scheme'] ?? ''));
            $host = strtolower((string) ($parts['host'] ?? ''));
        }

        if ($isLive && $scheme !== 'https') {
            throw new RuntimeException('STK callback URL must be public HTTPS.');
        }

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.local')) {
            throw new RuntimeException('STK callback URL must be public HTTPS.');
        }

        return $callbackUrl;
    }

    private function formatHttpException(\Throwable $exception): string
    {
        $message = $exception->getMessage();
        $response = $this->extractHttpResponse($exception);

        if ($response) {
            $body = $this->extractHttpBody($response);
            $errorCode = $body['errorCode'] ?? null;
            $errorMessage = $body['errorMessage'] ?? null;
            $requestId = $body['requestId'] ?? null;

            if ($errorCode || $errorMessage || $requestId) {
                $parts = [];
                if ($errorCode) {
                    $parts[] = "code {$errorCode}";
                }
                if ($errorMessage) {
                    $parts[] = $errorMessage;
                }
                if ($requestId) {
                    $parts[] = "requestId {$requestId}";
                }
                $message = 'M-Pesa API error: ' . implode(' | ', $parts);

                if ((string) $errorCode === '500.001.1001') {
                    $config = $this->config();
                    $environment = $config['environment'] ?? 'sandbox';
                    $shortcode = $config['shortcode'] ?? 'N/A';
                    $transactionType = $config['transaction_type'] ?? 'CustomerPayBillOnline';
                    $message .= " | Confirm environment ({$environment}), transaction type ({$transactionType}), shortcode ({$shortcode}), and passkey belong to the same Daraja app. If a recent STK prompt is still pending on this phone, wait 2 minutes and retry.";
                }
            } else {
                $status = method_exists($response, 'status') ? $response->status() : null;
                if ($status) {
                    $message = "M-Pesa API error (HTTP {$status}).";
                }
            }
        }

        return $message;
    }

    private function extractHttpResponse(\Throwable $exception): mixed
    {
        if (method_exists($exception, 'response')) {
            return $exception->response();
        }
        if (property_exists($exception, 'response')) {
            return $exception->response;
        }

        return null;
    }

    private function isWrongCredentialsError(\Throwable $exception): bool
    {
        $response = $this->extractHttpResponse($exception);
        if (!$response) {
            return false;
        }
        $body = $this->extractHttpBody($response);

        return (string) ($body['errorCode'] ?? '') === '500.001.1001';
    }

    private function extractHttpBody(mixed $response): array
    {
        try {
            $body = $response->json();
            if (is_array($body) && $body !== []) {
                return $body;
            }
        } catch (\Throwable $exception) {
            // Fall through to raw body decoding.
        }

        try {
            $raw = (string) $response->body();
        } catch (\Throwable $exception) {
            return [];
        }

        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function sendStkPushRequest(array $body): array
    {
        return Http::timeout(30)
            ->withToken($this->accessToken())
            ->asJson()
            ->acceptJson()
            ->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', $body)
            ->throw()
            ->json();
    }

    public function stkPush(array $payload): array
    {
        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;
        $passkey = $config['passkey'] ?? null;

        if (!$shortcode || !$passkey) {
            throw new \RuntimeException('M-Pesa shortcode/passkey are missing.');
        }

        $transactionType = $config['transaction_type'] ?? 'CustomerPayBillOnline';
        $partyB = $config['party_b'] ?? $config['till_number'] ?? $shortcode;
        $phone = $this->validatePhone((string) ($payload['PhoneNumber'] ?? $payload['PartyA'] ?? ''));
        $amount = $this->normalizeAmount($payload['Amount'] ?? 1);
        $callbackUrl = $this->sanitizeCallbackUrl((string) ($payload['CallBackURL'] ?? ($config['callback_url'] ?? '')));
        $accountReference = substr(trim((string) ($payload['AccountReference'] ?? 'NaiPrints')), 0, 12);
        $transactionDesc = substr(trim((string) ($payload['TransactionDesc'] ?? 'Payment')), 0, 13);

        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);

        $body = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $transactionType,
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => (string) $partyB,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference !== '' ? $accountReference : 'NaiPrints',
            'TransactionDesc' => $transactionDesc !== '' ? $transactionDesc : 'Payment',
        ];

        try {
            return $this->sendStkPushRequest($body);
        } catch (\Throwable $exception) {
            if ($this->isWrongCredentialsError($exception)) {
                Cache::forget($this->tokenCacheKey());
                try {
                    return $this->sendStkPushRequest($body);
                } catch (\Throwable $retryException) {
                    throw new RuntimeException($this->formatHttpException($retryException), previous: $retryException);
                }
            }

            throw new RuntimeException($this->formatHttpException($exception), previous: $exception);
        }
    }

    public function registerC2B(string $confirmationUrl, string $validationUrl, string $responseType = 'Completed'): array
    {
        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;

        if (!$shortcode) {
            throw new \RuntimeException('M-Pesa shortcode is missing.');
        }

        $body = [
            'ShortCode' => $shortcode,
            'ResponseType' => $responseType,
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl,
        ];

        return Http::timeout(30)
            ->withToken($this->accessToken())
            ->post($this->baseUrl() . '/mpesa/c2b/v1/registerurl', $body)
            ->throw()
            ->json();
    }
}
