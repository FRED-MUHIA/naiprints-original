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
        'theme_color',
        'footer_text',
        'payment_methods',
    ];

    protected $casts = [
        'payment_methods' => 'array',
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
        ];
    }
}
