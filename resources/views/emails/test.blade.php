<div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin: 0 0 12px;">SMTP Test Email</h2>
    <p style="margin: 0 0 8px;">This is a test message from {{ $appName ?? 'Nai Prints' }}.</p>
    <p style="margin: 0 0 8px;">Sent at: <strong>{{ $sentAt }}</strong></p>
    <p style="margin: 0 0 8px;">App URL: <strong>{{ $appUrl }}</strong></p>
    <p style="margin: 0 0 8px;">Mailer: <strong>{{ $mailer }}</strong></p>
    <p style="margin: 0 0 8px;">From: <strong>{{ $fromAddress }}</strong></p>
    <p style="margin: 0;">If you received this email, SMTP is working.</p>
</div>
