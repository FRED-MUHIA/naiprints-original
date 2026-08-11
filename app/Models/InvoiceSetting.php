<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    public const DEFAULT_PHONE = '254745506619';
    public const DEFAULT_EMAIL = 'info@naiprints.co.ke';
    public const DEFAULT_LOCATION = 'Kijabe streets, Norfolk Towers.';

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
            'phone' => self::DEFAULT_PHONE,
            'email' => self::DEFAULT_EMAIL,
            'website' => '',
            'theme_color' => '#e11d2e',
            'footer_text' => 'Thank you for choosing Nai Prints.',
            'payment_methods' => ['M-Pesa', 'Bank Transfer', 'Cash'],
            'bank_details' => '',
        ];
    }

    public function documentContactLines(bool $includeWebsite = true): array
    {
        $lines = [
            trim((string) $this->phone) ?: self::DEFAULT_PHONE,
            trim((string) $this->email) ?: self::DEFAULT_EMAIL,
            self::DEFAULT_LOCATION,
        ];

        if ($includeWebsite) {
            $website = trim((string) $this->website);
            if ($website !== '') {
                $lines[] = $website;
            }
        }

        return $lines;
    }
}
