<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Affiliate Policies | Nai Prints</title>
        <meta name="description" content="Nai Prints affiliate program policies, acceptable use, anti-scam rules, commissions, payouts, and data protection commitments.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

        <main class="mx-auto max-w-4xl px-4 py-12">
            <p class="section-kicker">Affiliate Program</p>
            <h1 class="section-title">Affiliate Policies</h1>

            <section class="mt-8 grid gap-6 text-sm leading-7 text-slate-700">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">1. Honest promotion only</h2>
                    <p class="mt-3">
                        Affiliates may share Nai Prints products using their approved referral link, but all promotions must be truthful,
                        lawful, and clear. Affiliates must not claim false discounts, fake guarantees, fake employment, fake giveaways,
                        or pretend to be Nai Prints staff unless written permission is given.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">2. No scams, spam, or abuse</h2>
                    <p class="mt-3">
                        Affiliate links must not be used for scamming, phishing, spam messages, fake orders, self-referrals, misleading
                        advertisements, unlawful content, impersonation, bot traffic, cookie stuffing, forced redirects, or any activity that
                        harms customers, Nai Prints, payment providers, or third parties.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">3. Data protection</h2>
                    <p class="mt-3">
                        Affiliates must protect customer data and may not collect, sell, expose, or misuse personal information. Any customer
                        details received during promotion must be handled securely and only for legitimate Nai Prints referral activity.
                        Affiliates should also read the full
                        <a class="font-semibold text-brand underline underline-offset-4" href="{{ route('data-protection-policies') }}">Data Protection Policies</a>.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">4. Commissions and payouts</h2>
                    <p class="mt-3">
                        Commissions are earned only on valid, completed, paid orders attributed to an affiliate link. Cancelled, refunded,
                        failed, fraudulent, or self-purchase orders may be rejected or reversed. Payouts are processed after admin approval
                        and once the minimum payout threshold is met.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">5. Account action</h2>
                    <p class="mt-3">
                        Nai Prints may reject commissions, suspend payouts, or close an affiliate account if the affiliate violates these
                        policies, abuses referral tracking, misleads customers, or uses the program in a way that creates legal, security,
                        payment, or reputation risk.
                    </p>
                </div>
            </section>

            <div class="mt-8 flex flex-wrap gap-3">
                <a class="btn-brand" href="{{ route('affiliate.register') }}">Back to registration</a>
                <a class="btn-outline" href="{{ route('ethical-policies') }}">Ethical policies</a>
            </div>
        </main>

        @include('partials.site-footer', [
            'globalFooter' => $globalFooter ?? [],
            'globalPurchasePopups' => $globalPurchasePopups ?? [],
            'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
        ])
    </body>
</html>
