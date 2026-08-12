<?php

namespace Tests\Unit;

use App\Models\InvoiceSetting;
use App\Services\MpesaService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use Tests\TestCase;

class MpesaServiceTest extends TestCase
{
    public function test_oauth_json_error_is_formatted_with_actionable_context(): void
    {
        $service = $this->service([
            'environment' => 'live',
            'consumer_key' => 'key',
            'consumer_secret' => 'secret',
        ]);

        $exception = new ClientException(
            'Client error',
            new Request('GET', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
            new Response(400, [], json_encode([
                'error' => 'invalid_client',
                'error_description' => 'Bad credentials',
            ]))
        );

        $this->assertSame(
            'M-Pesa API error: OAuth invalid_client | Bad credentials | Confirm consumer key/secret belong to the live Daraja app.',
            $this->invokePrivate($service, 'formatHttpException', $exception)
        );
    }

    public function test_oauth_plain_error_body_is_not_hidden_by_guzzle_message(): void
    {
        $service = $this->service([
            'environment' => 'sandbox',
            'consumer_key' => 'key',
            'consumer_secret' => 'secret',
        ]);

        $exception = new ClientException(
            'Client error',
            new Request('GET', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
            new Response(400, [], "Bad Request\n")
        );

        $this->assertSame(
            'M-Pesa OAuth failed with HTTP 400. Confirm the consumer key/secret belong to the selected Daraja environment. Safaricom response: Bad Request',
            $this->invokePrivate($service, 'formatHttpException', $exception)
        );
    }

    public function test_token_cache_key_changes_with_credentials(): void
    {
        $first = $this->service([
            'environment' => 'live',
            'consumer_key' => 'key',
            'consumer_secret' => 'secret-one',
        ]);
        $second = $this->service([
            'environment' => 'live',
            'consumer_key' => 'key',
            'consumer_secret' => 'secret-two',
        ]);

        $this->assertNotSame(
            $this->invokePrivate($first, 'tokenCacheKey'),
            $this->invokePrivate($second, 'tokenCacheKey')
        );
    }

    private function service(array $config): MpesaService
    {
        return new MpesaService(new InvoiceSetting([
            'mpesa_config' => $config,
        ]));
    }

    private function invokePrivate(MpesaService $service, string $method, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionClass($service);
        $reflectedMethod = $reflection->getMethod($method);
        $reflectedMethod->setAccessible(true);

        return $reflectedMethod->invoke($service, ...$arguments);
    }
}
