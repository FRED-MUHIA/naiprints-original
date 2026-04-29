<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="/images/logo.svg">
        <title>Data Protection Policies | Nai Prints</title>
        <meta name="description" content="Read the Nai Prints data protection policies for how client information, artwork, and order data are handled.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

        <main class="mx-auto max-w-4xl px-4 py-12">
            <p class="section-kicker">Data Protection</p>
            <h1 class="section-title">Nai Prints Data Protection Policies</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-700 md:text-base">
                How we collect, use, safeguard, and retain client information during printing, branding, design, and related services.
            </p>

            <section class="mt-8 grid gap-6 text-sm leading-7 text-slate-700">
                @foreach ([
                    ['1. Purpose', 'This Data Protection Policy explains how Nai Prints Corporation (“Nai Prints”, “we”, “us”, or “our”) handles personal information, client records, artwork, order details, and related business data provided by clients, website visitors, suppliers, and partners.'],
                    ['4. Lawful and fair handling', 'We handle client information fairly, transparently, and only for legitimate business purposes connected to requested services, contractual obligations, legal compliance, or client consent where consent is required.'],
                    ['5. Artwork, designs, and confidential files', 'Client artwork, logos, print files, and confidential design materials are treated as client-provided business information. Nai Prints does not sell client artwork or confidential files. Such files are used for design, approval, production, record keeping, and agreed service delivery only.'],
                    ['7. Data security', 'Nai Prints applies reasonable administrative, technical, and operational safeguards to protect information from unauthorized access, alteration, disclosure, loss, or misuse. Access to client information is limited to personnel or service providers who need it for approved business purposes.'],
                    ['8. Data retention', 'We retain information only for as long as reasonably necessary for service delivery, client support, business records, accounting, legal compliance, dispute resolution, and legitimate operational purposes. Where information is no longer required, it may be securely deleted, archived, or anonymized.'],
                    ['9. Client rights and requests', 'Clients may request access, correction, update, or deletion of their personal information, subject to identity verification and any applicable legal or business record-keeping requirements. Requests can be sent through the contact details available on our website.'],
                    ['10. Accuracy of information', 'Clients are responsible for providing accurate contact details, artwork, approval instructions, delivery information, and order specifications. Nai Prints may request clarification where information is incomplete, inconsistent, or unclear.'],
                    ['11. Website forms and communications', 'Information submitted through website forms, email, WhatsApp, phone, or other communication channels may be used to respond to enquiries, prepare quotes, process orders, provide support, and keep service records.'],
                    ['12. Policy updates', 'Nai Prints may update this Data Protection Policy from time to time to reflect changes in services, operations, legal requirements, or security practices. The latest version will be made available on this page.'],
                    ['13. Contact', 'For questions or data protection requests, please contact Nai Prints through the official contact channels listed on our website.'],
                ] as [$title, $body])
                    <div class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
                        <p class="mt-3">{{ $body }}</p>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">2. Information we collect</h2>
                    <p class="mt-3">We may collect information necessary to provide our services, including:</p>
                    <ul class="mt-3 list-disc space-y-2 pl-5">
                        <li>Client names, company names, contact persons, phone numbers, email addresses, and delivery details.</li>
                        <li>Order details, quotations, invoices, payment references, and transaction records.</li>
                        <li>Artwork, logos, images, design files, brand guidelines, and print specifications.</li>
                        <li>Website contact form submissions, support messages, and communication history.</li>
                        <li>Technical information such as IP address, browser details, and source URL where required for security and support.</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">3. How we use information</h2>
                    <p class="mt-3">Nai Prints uses collected information to:</p>
                    <ul class="mt-3 list-disc space-y-2 pl-5">
                        <li>Process orders, quotations, invoices, receipts, payments, and deliveries.</li>
                        <li>Prepare artwork, mockups, production files, and print specifications.</li>
                        <li>Communicate with clients about approvals, timelines, revisions, and support requests.</li>
                        <li>Maintain business records, accounting records, and service history.</li>
                        <li>Improve service quality, website performance, security, and client experience.</li>
                        <li>Comply with applicable legal, tax, accounting, and regulatory obligations.</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">6. Information sharing</h2>
                    <p class="mt-3">We may share limited information only where necessary, including with:</p>
                    <ul class="mt-3 list-disc space-y-2 pl-5">
                        <li>Delivery partners for order fulfillment.</li>
                        <li>Payment providers for transaction processing and verification.</li>
                        <li>Professional advisers, auditors, or authorities where legally required.</li>
                        <li>Trusted service providers who support website hosting, email, storage, production, or business operations.</li>
                    </ul>
                    <p class="mt-3 font-semibold">We do not sell client personal data to third parties.</p>
                </div>
            </section>
        </main>

        @include('partials.site-footer', [
            'globalFooter' => $globalFooter ?? [],
            'globalPurchasePopups' => $globalPurchasePopups ?? [],
            'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
        ])
    </body>
</html>
