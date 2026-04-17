<?php

namespace App\Jobs;

use App\Services\MpesaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMpesaCallbackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(public array $payload)
    {
    }

    public function handle(MpesaService $mpesa): void
    {
        try {
            $transaction = $mpesa->handleCallback($this->payload);

            logger()->info('Processed M-Pesa callback', [
                'transaction_id' => $transaction->id,
                'checkout_request_id' => $transaction->checkout_request_id,
                'status' => $transaction->status,
                'result_code' => $transaction->result_code,
            ]);
        } catch (\Throwable $exception) {
            logger()->error('M-Pesa callback processing failed', [
                'payload' => $this->payload,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
