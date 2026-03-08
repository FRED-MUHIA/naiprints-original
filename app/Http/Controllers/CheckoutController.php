<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\GlobalSection;
use App\Support\PageDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:180'],
            'phone' => ['required', 'string', 'max:60'],
            'address' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment' => ['required', 'string', 'max:40'],
            'cart_items' => ['required', 'string'],
            'cart_subtotal' => ['nullable', 'numeric', 'min:0'],
            'cart_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = json_decode($validated['cart_items'], true);
        if (!is_array($items)) {
            return back()->withErrors(['cart_items' => 'We could not read your cart items.'])->withInput();
        }

        $cleanItems = collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(function ($item) {
                $qty = (int) ($item['qty'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                return [
                    'id' => (string) ($item['id'] ?? ''),
                    'name' => trim((string) ($item['name'] ?? 'Product')),
                    'price' => max(0, $price),
                    'qty' => max(0, $qty),
                    'image' => trim((string) ($item['image'] ?? '')),
                    'url' => trim((string) ($item['url'] ?? '')),
                    'color' => trim((string) ($item['color'] ?? '')),
                    'size' => trim((string) ($item['size'] ?? '')),
                ];
            })
            ->filter(fn ($item) => $item['qty'] > 0 && $item['name'] !== '')
            ->values();

        if ($cleanItems->isEmpty()) {
            return back()->withErrors(['cart_items' => 'Your cart is empty.'])->withInput();
        }

        $subtotal = $cleanItems->reduce(
            fn ($carry, $item) => $carry + ($item['price'] * $item['qty']),
            0.0
        );

        $orderNumber = 'NP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        $order = [
            'number' => $orderNumber,
            'placed_at' => now(),
            'customer' => [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ],
            'shipping' => [
                'address' => $validated['address'],
                'city' => $validated['city'],
                'notes' => $validated['notes'] ?? '',
            ],
            'payment' => $validated['payment'],
            'items' => $cleanItems->all(),
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];

        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $logoPath = $header->content['logo_url'] ?? '/images/logo.svg';
        $logoUrl = url($logoPath ?: '/images/logo.svg');

        $contactLinks = collect($footer->content['columns'] ?? [])
            ->flatMap(fn ($column) => $column['links'] ?? [])
            ->filter(fn ($link) => is_array($link))
            ->values();

        $contactEmail = $contactLinks->first(function ($link) {
            $label = (string) ($link['label'] ?? '');
            $url = (string) ($link['url'] ?? '');
            return str_contains($label, '@') || str_contains($url, '@');
        });

        $contactPhone = $contactLinks->first(function ($link) {
            $label = (string) ($link['label'] ?? '');
            return preg_match('/\\d{6,}/', $label);
        });

        $support = [
            'brand' => $footer->content['brand'] ?? 'Nai Prints',
            'email' => $contactEmail['label'] ?? config('mail.from.address', 'hello@naiprints.co.ke'),
            'phone' => $contactPhone['label'] ?? '0700 000 000',
            'logo_url' => $logoUrl,
        ];

        Mail::to($validated['email'])
            ->send(new OrderPlaced($order, $support));

        return redirect()
            ->route('checkout')
            ->with('order_success', 'Order placed! A confirmation email has been sent to ' . $validated['email'] . '.');
    }
}
