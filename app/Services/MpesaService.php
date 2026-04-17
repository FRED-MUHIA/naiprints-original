<?php

namespace App\Services;

use App\Models\InvoiceSetting;
use App\Models\MpesaTransaction;
use App\Support\MpesaPhone;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class MpesaService
{
    public function __construct(private ?InvoiceSetting $settings = null)
    {
    }

    public function config(): array
    {
        $settings = $this->settings ?: InvoiceSetting::first();
        $raw = [];

        $this->mergeConfig($raw, config('services.mpesa', []));
        $this->mergeConfig($raw, config('mpesa', []));
        $this->mergeConfig($raw, is_array($settings?->mpesa_config) ? $settings->mpesa_config : []);

        if (!empty($raw['environment']) && is_string($raw['environment'])) {
            $raw['environment'] = strtolower($raw['environment']);
        }

        foreach (['shortcode', 'party_b', 'till_number'] as $numericKey) {
            if (!empty($raw[$numericKey]) && is_string($raw[$numericKey])) {
                $raw[$numericKey] = preg_replace('/\D+/', '', $raw[$numericKey]);
            }
        }

        return $raw;
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

    public function generateAccessToken(bool $forceRefresh = false): string
    {
        $config = $this->config();
        $key = $config['consumer_key'] ?? null;
        $secret = $config['consumer_secret'] ?? null;

        if (!$key || !$secret) {
            throw new RuntimeException('M-Pesa consumer key/secret are missing.');
        }

        if ($forceRefresh) {
            Cache::forget($this->tokenCacheKey());
        }

        return Cache::remember(
            $this->tokenCacheKey(),
            now()->addMinutes((int) ($config['cache_ttl_minutes'] ?? 55)),
            function () use ($key, $secret) {
                try {
                    $response = $this->httpClient()->request('GET', '/oauth/v1/generate', [
                        'auth' => [$key, $secret],
                        'query' => ['grant_type' => 'client_credentials'],
                    ]);
                } catch (\Throwable $exception) {
                    throw new RuntimeException($this->formatHttpException($exception), previous: $exception);
                }

                $payload = $this->decodeResponseBody((string) $response->getBody());
                $token = $payload['access_token'] ?? null;

                if (!$token || !is_string($token)) {
                    throw new RuntimeException('Failed to obtain M-Pesa access token.');
                }

                return $token;
            }
        );
    }

    public function accessToken(): string
    {
        return $this->generateAccessToken();
    }

    public function generatePassword(?string $timestamp = null): string
    {
        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;
        $passkey = $config['passkey'] ?? null;

        if (!$shortcode || !$passkey) {
            throw new RuntimeException('M-Pesa shortcode/passkey are missing or invalid. Set the real Daraja passkey (not "undefined") in server .env (MPESA_PASSKEY) or admin settings.');
        }

        $timestamp = $timestamp ?: Carbon::now()->format('YmdHis');

        return base64_encode($shortcode . $passkey . $timestamp);
    }

    public function normalizePhone(string $phone): string
    {
        return MpesaPhone::format($phone) ?? trim($phone);
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

    public function stkPush(array $payload): array
    {
        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;

        if (!$shortcode) {
            throw new RuntimeException('M-Pesa shortcode is missing.');
        }

        $transactionType = $config['transaction_type'] ?? 'CustomerPayBillOnline';
        $partyB = $config['party_b'] ?? $config['till_number'] ?? $shortcode;
        $phone = $this->validatePhone((string) ($payload['PhoneNumber'] ?? $payload['PartyA'] ?? ''));
        $amount = $this->normalizeAmount($payload['Amount'] ?? 1);
        $callbackUrl = $this->sanitizeCallbackUrl((string) ($payload['CallBackURL'] ?? ($config['callback_url'] ?? '')));
        $accountReference = substr(trim((string) ($payload['AccountReference'] ?? 'NaiPrints')), 0, 12);
        $transactionDesc = substr(trim((string) ($payload['TransactionDesc'] ?? 'Payment')), 0, 13);

        $timestamp = Carbon::now()->format('YmdHis');
        $password = $this->generatePassword($timestamp);

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

    public function handleCallback(array $payload): MpesaTransaction
    {
        $callback = data_get($payload, 'Body.stkCallback');

        if (!is_array($callback)) {
            throw new RuntimeException('Invalid callback payload.');
        }

        $checkoutId = (string) ($callback['CheckoutRequestID'] ?? '');
        $transaction = $checkoutId !== ''
            ? MpesaTransaction::firstOrNew(['checkout_request_id' => $checkoutId])
            : new MpesaTransaction();

        $metadata = collect($callback['CallbackMetadata']['Item'] ?? [])->mapWithKeys(function ($item) {
            return [(string) ($item['Name'] ?? '') => $item['Value'] ?? null];
        });

        $resultCode = (string) ($callback['ResultCode'] ?? '');
        $resultDesc = (string) ($callback['ResultDesc'] ?? '');
        $status = $this->mapResultCodeToStatus($resultCode);

        $transactionDate = $metadata->get('TransactionDate');
        $occurredAt = null;
        if ($transactionDate) {
            try {
                $occurredAt = Carbon::createFromFormat('YmdHis', (string) $transactionDate);
            } catch (\Throwable $exception) {
                $occurredAt = now();
            }
        }

        $transaction->fill([
            'merchant_request_id' => (string) ($callback['MerchantRequestID'] ?? $transaction->merchant_request_id),
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'status' => $status,
            'phone' => $metadata->get('PhoneNumber') ?: $transaction->phone,
            'receipt_number' => $metadata->get('MpesaReceiptNumber') ?: $transaction->receipt_number,
            'reference' => $metadata->get('AccountReference') ?: $transaction->reference,
            'raw_callback' => $payload,
        ]);

        $amount = $metadata->get('Amount');
        if (is_numeric($amount)) {
            $transaction->amount = (float) $amount;
        }

        if ($occurredAt) {
            $transaction->occurred_at = $occurredAt;
        }

        if ($transaction->type === null || $transaction->type === '') {
            $transaction->type = 'stk';
        }

        $transaction->save();

        return $transaction->fresh();
    }

    public function validateTransaction(string $checkoutRequestId): array
    {
        $checkoutRequestId = trim($checkoutRequestId);
        if ($checkoutRequestId === '') {
            throw new RuntimeException('CheckoutRequestID is required.');
        }

        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;

        if (!$shortcode) {
            throw new RuntimeException('M-Pesa shortcode is missing.');
        }

        $timestamp = Carbon::now()->format('YmdHis');
        $body = [
            'BusinessShortCode' => $shortcode,
            'Password' => $this->generatePassword($timestamp),
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        try {
            $response = $this->httpClient()->request('POST', '/mpesa/stkpushquery/v1/query', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->generateAccessToken(),
                    'Accept' => 'application/json',
                ],
                'json' => $body,
            ]);
            $payload = $this->decodeResponseBody((string) $response->getBody());
        } catch (\Throwable $exception) {
            throw new RuntimeException($this->formatHttpException($exception), previous: $exception);
        }

        $resultCode = isset($payload['ResultCode']) ? (string) $payload['ResultCode'] : null;
        $status = $this->mapResultCodeToStatus($resultCode);

        $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->latest('id')->first();
        if ($transaction) {
            $transaction->status = $status;
            $transaction->result_code = $resultCode;
            $transaction->result_desc = (string) ($payload['ResultDesc'] ?? $payload['ResponseDescription'] ?? $transaction->result_desc);
            $transaction->raw_callback = array_merge((array) $transaction->raw_callback, [
                'query' => $payload,
            ]);
            $transaction->save();
        }

        return [
            'checkout_request_id' => $checkoutRequestId,
            'status' => $status,
            'result_code' => $resultCode,
            'result_desc' => (string) ($payload['ResultDesc'] ?? $payload['ResponseDescription'] ?? ''),
            'raw' => $payload,
        ];
    }

    public function registerC2B(string $confirmationUrl, string $validationUrl, string $responseType = 'Completed'): array
    {
        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;

        if (!$shortcode) {
            throw new RuntimeException('M-Pesa shortcode is missing.');
        }

        $body = [
            'ShortCode' => $shortcode,
            'ResponseType' => $responseType,
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl,
        ];

        try {
            $response = $this->httpClient()->request('POST', '/mpesa/c2b/v1/registerurl', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->generateAccessToken(),
                    'Accept' => 'application/json',
                ],
                'json' => $body,
            ]);

            return $this->decodeResponseBody((string) $response->getBody());
        } catch (\Throwable $exception) {
            throw new RuntimeException($this->formatHttpException($exception), previous: $exception);
        }
    }

    private function mergeConfig(array &$target, mixed $source): void
    {
        if (!is_array($source)) {
            return;
        }

        foreach ($source as $key => $value) {
            $value = $this->normalizeConfigValue($value);
            if ($value === null || $value === '') {
                continue;
            }
            $target[(string) $key] = $value;
        }
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

    private function timeoutSeconds(): int
    {
        $config = $this->config();

        return max(5, (int) ($config['request_timeout'] ?? 30));
    }

    private function httpClient(): Client
    {
        return new Client([
            'base_uri' => $this->baseUrl(),
            'timeout' => $this->timeoutSeconds(),
        ]);
    }

    private function decodeResponseBody(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function sendStkPushRequest(array $body): array
    {
        $response = $this->httpClient()->request('POST', '/mpesa/stkpush/v1/processrequest', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateAccessToken(),
                'Accept' => 'application/json',
            ],
            'json' => $body,
        ]);

        return $this->decodeResponseBody((string) $response->getBody());
    }

    private function mapResultCodeToStatus(?string $resultCode): string
    {
        if ($resultCode === null || $resultCode === '') {
            return MpesaTransaction::STATUS_PENDING;
        }

        return $resultCode === '0'
            ? MpesaTransaction::STATUS_SUCCESS
            : MpesaTransaction::STATUS_FAILED;
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
            }
        }

        return $message;
    }

    private function extractHttpResponse(\Throwable $exception): mixed
    {
        if (method_exists($exception, 'getResponse')) {
            return $exception->getResponse();
        }

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
        if (is_object($response) && method_exists($response, 'json')) {
            try {
                $body = $response->json();
                if (is_array($body) && $body !== []) {
                    return $body;
                }
            } catch (\Throwable $exception) {
                // Fall through to raw body decoding.
            }
        }

        $raw = '';

        if (is_object($response) && method_exists($response, 'getBody')) {
            try {
                $raw = (string) $response->getBody();
            } catch (\Throwable $exception) {
                $raw = '';
            }
        } elseif (is_object($response) && method_exists($response, 'body')) {
            try {
                $raw = (string) $response->body();
            } catch (\Throwable $exception) {
                $raw = '';
            }
        }

        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
