<?php

namespace App\Services;

use App\Models\InvoiceSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MpesaService
{
    public function __construct(private ?InvoiceSetting $settings = null)
    {
    }

    public function config(): array
    {
        $settings = $this->settings ?: InvoiceSetting::first();
        return $settings?->mpesa_config ?? [];
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

        $cacheKey = 'mpesa_token_' . ($config['environment'] ?? 'sandbox');

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
        $digits = preg_replace('/\D+/', '', $phone);
        if (!$digits) {
            return $phone;
        }

        if (Str::startsWith($digits, '0')) {
            return '254' . substr($digits, 1);
        }

        if (Str::startsWith($digits, '7') && strlen($digits) === 9) {
            return '254' . $digits;
        }

        if (Str::startsWith($digits, '254')) {
            return $digits;
        }

        return $digits;
    }

    public function stkPush(array $payload): array
    {
        $config = $this->config();
        $shortcode = $config['shortcode'] ?? null;
        $passkey = $config['passkey'] ?? null;

        if (!$shortcode || !$passkey) {
            throw new \RuntimeException('M-Pesa shortcode/passkey are missing.');
        }

        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);

        $body = array_merge([
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $config['transaction_type'] ?? 'CustomerPayBillOnline',
            'PartyB' => $shortcode,
        ], $payload);

        return Http::timeout(30)
            ->withToken($this->accessToken())
            ->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', $body)
            ->throw()
            ->json();
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
