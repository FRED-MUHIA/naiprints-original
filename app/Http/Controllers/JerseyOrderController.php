<?php

namespace App\Http\Controllers;

use App\Models\Jersey;
use App\Models\JerseyOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JerseyOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jersey_id' => ['required', 'integer', 'exists:jerseys,id'],
            'design_id' => ['nullable', 'integer', 'exists:jersey_designs,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email:rfc,dns', 'max:190'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'sizes' => ['required', 'array'],
            'sizes.*' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'extra_logos' => ['nullable', 'integer', 'min:0', 'max:20'],
            'custom_name_number' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $jersey = Jersey::query()->findOrFail($validated['jersey_id']);
        $sizes = collect($validated['sizes'] ?? [])->map(fn ($value) => max(0, (int) $value))->all();
        $totalQuantity = array_sum($sizes);
        if ($totalQuantity <= 0) {
            throw ValidationException::withMessages([
                'sizes' => 'At least one jersey quantity is required.',
            ]);
        }

        $extraLogos = (int) ($validated['extra_logos'] ?? 0);
        $hasCustomNameAndNumber = (bool) ($validated['custom_name_number'] ?? false);
        $basePrice = (float) ($jersey->base_price ?? 0);
        $extraLogoFee = (float) config('jersey.pricing.extra_logo_fee', 120);
        $customFee = (float) config('jersey.pricing.custom_name_number_fee', 180);

        $unitPrice = $basePrice + ($extraLogos * $extraLogoFee) + ($hasCustomNameAndNumber ? $customFee : 0);
        $totalAmount = $unitPrice * $totalQuantity;

        $pricing = [
            'base_price' => $basePrice,
            'extra_logo_fee' => $extraLogoFee,
            'extra_logos' => $extraLogos,
            'custom_name_number_fee' => $customFee,
            'custom_name_number' => $hasCustomNameAndNumber,
            'unit_price' => round($unitPrice, 2),
            'quantity' => $totalQuantity,
            'total_amount' => round($totalAmount, 2),
        ];

        $order = JerseyOrder::query()->create([
            'public_id' => strtoupper(Str::random(12)),
            'user_id' => $request->user()?->id,
            'jersey_id' => $jersey->id,
            'jersey_design_id' => $validated['design_id'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'sizes' => $sizes,
            'pricing' => $pricing,
            'total_amount' => $pricing['total_amount'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => [
                'order_id' => $order->public_id,
                'status' => $order->status,
                'pricing' => $pricing,
            ],
        ]);
    }
}
