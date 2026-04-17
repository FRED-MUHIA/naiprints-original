<?php

namespace App\Support;

class MpesaPhone
{
    public static function format(?string $phone): ?string
    {
        $value = trim((string) $phone);
        if ($value === '') {
            return null;
        }

        if (preg_match('/(?:\\+|00)?254\\s*[-\\s()]?0?([17]\\d{8})\\b/u', $value, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/\\b0([17]\\d{8})\\b/u', $value, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/\\b([17]\\d{8})\\b/u', $value, $matches)) {
            return '254' . $matches[1];
        }

        $digits = preg_replace('/\\D+/', '', $value) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00254')) {
            $digits = substr($digits, 2);
        }

        if (preg_match('/^2540([17]\\d{8})$/', $digits, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/^0([17]\\d{8})$/', $digits, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/^([17]\\d{8})$/', $digits, $matches)) {
            return '254' . $matches[1];
        }

        if (preg_match('/^254[17]\\d{8}$/', $digits)) {
            return $digits;
        }

        return null;
    }
}
