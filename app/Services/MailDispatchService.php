<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MailDispatchService
{
    public function normalizeEmail(?string $candidate): ?string
    {
        $value = trim((string) $candidate);
        if ($value === '') {
            return null;
        }

        if (str_starts_with(Str::lower($value), 'mailto:')) {
            $value = preg_replace('/^mailto:/i', '', $value) ?? '';
            $value = explode('?', $value, 2)[0] ?? '';
        }

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches)) {
            $value = $matches[0];
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return Str::lower($value);
    }

    public function normalizePhone(?string $candidate): ?string
    {
        $value = trim((string) $candidate);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/\+?\d[\d\s\-\(\)]{5,}/', $value, $matches)) {
            return null;
        }

        return trim($matches[0]);
    }

    /**
     * @param array<int, ?string> $candidates
     * @return array<int, string>
     */
    public function uniqueRecipients(array $candidates): array
    {
        return collect($candidates)
            ->map(fn ($candidate) => $this->normalizeEmail($candidate))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, ?string> $candidates
     * @param callable(): \Illuminate\Mail\Mailable $mailableFactory
     * @return array{sent: array<int, string>, failed: array<int, string>}
     */
    public function sendToRecipients(array $candidates, callable $mailableFactory, array $context = []): array
    {
        $sent = [];
        $failed = [];

        foreach ($this->uniqueRecipients($candidates) as $recipient) {
            try {
                Mail::to($recipient)->send($mailableFactory());
                $sent[] = $recipient;
            } catch (\Throwable $exception) {
                $failed[] = $recipient;
                logger()->error('Email delivery failed', array_merge($context, [
                    'recipient' => $recipient,
                    'mailer' => config('mail.default'),
                    'error' => $exception->getMessage(),
                ]));
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    public function resolveSupportContact(array $footerContent = [], ?string $logoUrl = null): array
    {
        $links = collect($footerContent['columns'] ?? [])
            ->flatMap(fn ($column) => $column['links'] ?? [])
            ->filter(fn ($link) => is_array($link))
            ->values();

        $configuredEmail = $this->normalizeEmail((string) config('mail.support.address'))
            ?? $this->normalizeEmail((string) config('mail.from.address'));

        $footerEmail = $links
            ->map(fn (array $link) => $this->extractEmailFromLink($link))
            ->first(fn ($value) => !empty($value));

        $email = $configuredEmail ?: ($footerEmail ?: 'info@naiprints.co.ke');

        $phone = $links
            ->map(function (array $link) {
                return $this->normalizePhone((string) ($link['label'] ?? ''))
                    ?? $this->normalizePhone((string) ($link['url'] ?? ''));
            })
            ->first(fn ($value) => !empty($value));

        return [
            'brand' => (string) ($footerContent['brand'] ?? config('mail.from.name', config('app.name', 'Nai Prints'))),
            'email' => $email,
            'phone' => $phone ?: '0700 000 000',
            'logo_url' => $logoUrl ?: (string) ($footerContent['logo_url'] ?? ''),
        ];
    }

    private function extractEmailFromLink(array $link): ?string
    {
        return $this->normalizeEmail((string) ($link['label'] ?? ''))
            ?? $this->normalizeEmail((string) ($link['url'] ?? ''));
    }
}
