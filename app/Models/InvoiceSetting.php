<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $fillable = [
        'business_name',
        'phone',
        'email',
        'website',
        'logo_path',
        'signature_path',
        'bank_details',
        'theme_color',
        'footer_text',
        'payment_methods',
        'mpesa_config',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'mpesa_config' => 'array',
    ];

    public static function defaults(): array
    {
        return [
            'business_name' => 'Nai Prints',
            'phone' => '',
            'email' => '',
            'website' => '',
            'theme_color' => '#e11d2e',
            'footer_text' => 'Thank you for choosing Nai Prints.',
            'payment_methods' => ['M-Pesa', 'Bank Transfer', 'Cash'],
            'bank_details' => '',
        ];
    }
}
