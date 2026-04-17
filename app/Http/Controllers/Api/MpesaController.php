<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMpesaCallbackJob;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public function __construct(private readonly MpesaService $mpesa)
    {
    }

    public function stkPush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reference' => ['required', 'string', 'max:64'],
            'description' => ['nullable', 'string', 'max:100'],
            'callback_url' => ['nullable', 'url', 'max:255'],
        ]);

        try {
            $phone = $this->mpesa->validatePhone((string) $data['phone']);
            $amount = $this->mpesa->normalizeAmount($data['amount']);
            $response = $this->mpesa->stkPush([
                'Amount' => $amount,
                'PhoneNumber' => $phone,
                'PartyA' => $phone,
                'CallBackURL' => (string) ($data['callback_url'] ?? ''),
                'AccountReference' => (string) $data['reference'],
                'TransactionDesc' => (string) ($data['description'] ?? 'Payment'),
            ]);

            $transaction = MpesaTransaction::create([
                'type' => 'stk',
                'phone' => $phone,
                'amount' => $amount,
                'reference' => (string) $data['reference'],
                'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                'status' => MpesaTransaction::STATUS_PENDING,
                'result_desc' => $response['ResponseDescription'] ?? null,
                'raw_request' => [
                    'request' => $data,
                    'response' => $response,
                ],
            ]);
        } catch (\Throwable $exception) {
            logger()->error('API STK push failed', [
                'phone' => $data['phone'] ?? null,
                'amount' => $data['amount'] ?? null,
                'reference' => $data['reference'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'STK push failed.',
                'error' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'STK push sent.',
            'transaction_id' => $transaction->id,
            'checkout_request_id' => $transaction->checkout_request_id,
            'merchant_request_id' => $transaction->merchant_request_id,
            'status' => $transaction->status,
        ], 202);
    }

    public function callback(Request $request): JsonResponse
    {
        ProcessMpesaCallbackJob::dispatch($request->all());

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    public function status(string $checkoutId): JsonResponse
    {
        $transaction = MpesaTransaction::checkout($checkoutId)->latest('id')->first();
        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        if ($transaction->status === MpesaTransaction::STATUS_PENDING) {
            try {
                $statusPayload = $this->mpesa->validateTransaction($checkoutId);
                $transaction->refresh();
            } catch (\Throwable $exception) {
                logger()->warning('API STK status query failed', [
                    'checkout_request_id' => $checkoutId,
                    'error' => $exception->getMessage(),
                ]);

                $statusPayload = [
                    'status' => $transaction->status,
                    'result_code' => $transaction->result_code,
                    'result_desc' => $transaction->result_desc,
                ];
            }
        } else {
            $statusPayload = [
                'status' => $transaction->status,
                'result_code' => $transaction->result_code,
                'result_desc' => $transaction->result_desc,
            ];
        }

        return response()->json([
            'checkout_request_id' => $checkoutId,
            'transaction_id' => $transaction->id,
            'status' => $statusPayload['status'] ?? $transaction->status,
            'result_code' => $statusPayload['result_code'] ?? $transaction->result_code,
            'result_desc' => $statusPayload['result_desc'] ?? $transaction->result_desc,
            'phone' => $transaction->phone,
            'amount' => (float) $transaction->amount,
            'reference' => $transaction->reference,
            'receipt_number' => $transaction->receipt_number,
            'updated_at' => optional($transaction->updated_at)->toIso8601String(),
        ]);
    }
}
