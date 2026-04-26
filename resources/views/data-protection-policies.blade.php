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
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-6">
                <section class="hero-card ethical-policy-hero">
                    <p class="pill">Data Protection</p>
                    <h1 class="mt-6 text-4xl font-semibold leading-tight text-white md:text-5xl">
                        Nai Prints Data Protection Policies
                    </h1>
                    <p class="mt-4 max-w-3xl text-sm text-white/85 md:text-base">
                        How we collect, use, safeguard, and retain client information during printing, branding, design, and related services.
                    </p>
                </section>

                <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-10">
                    <div class="prose max-w-none text-justify prose-slate prose-headings:text-left prose-headings:text-[#0b1f4d] prose-a:text-brand">
                        <h2>1. Purpose</h2>
                        <p>
                            This Data Protection Policy explains how Nai Prints Corporation (“Nai Prints”, “we”, “us”, or “our”) handles personal information, client records, artwork, order details, and related business data provided by clients, website visitors, suppliers, and partners.
                        </p>

                        <h2>2. Information We Collect</h2>
                        <p>We may collect information necessary to provide our services, including:</p>
                        <ul>
                            <li>Client names, company names, contact persons, phone numbers, email addresses, and delivery details.</li>
                            <li>Order details, quotations, invoices, payment references, and transaction records.</li>
                            <li>Artwork, logos, images, design files, brand guidelines, and print specifications.</li>
                            <li>Website contact form submissions, support messages, and communication history.</li>
                            <li>Technical information such as IP address, browser details, and source URL where required for security and support.</li>
                        </ul>

                        <h2>3. How We Use Information</h2>
                        <p>Nai Prints uses collected information to:</p>
                        <ul>
                            <li>Process orders, quotations, invoices, receipts, payments, and deliveries.</li>
                            <li>Prepare artwork, mockups, production files, and print specifications.</li>
                            <li>Communicate with clients about approvals, timelines, revisions, and support requests.</li>
                            <li>Maintain business records, accounting records, and service history.</li>
                            <li>Improve service quality, website performance, security, and client experience.</li>
                            <li>Comply with applicable legal, tax, accounting, and regulatory obligations.</li>
                        </ul>

                        <h2>4. Lawful and Fair Handling</h2>
                        <p>
                            We handle client information fairly, transparently, and only for legitimate business purposes connected to requested services, contractual obligations, legal compliance, or client consent where consent is required.
                        </p>

                        <h2>5. Artwork, Designs, and Confidential Files</h2>
                        <p>
                            Client artwork, logos, print files, and confidential design materials are treated as client-provided business information. Nai Prints does not sell client artwork or confidential files. Such files are used for design, approval, production, record keeping, and agreed service delivery only.
                        </p>

                        <h2>6. Information Sharing</h2>
                        <p>We may share limited information only where necessary, including with:</p>
                        <ul>
                            <li>Delivery partners for order fulfillment.</li>
                            <li>Payment providers for transaction processing and verification.</li>
                            <li>Professional advisers, auditors, or authorities where legally required.</li>
                            <li>Trusted service providers who support website hosting, email, storage, production, or business operations.</li>
                        </ul>
                        <p>We do not sell client personal data to third parties.</p>

                        <h2>7. Data Security</h2>
                        <p>
                            Nai Prints applies reasonable administrative, technical, and operational safeguards to protect information from unauthorized access, alteration, disclosure, loss, or misuse. Access to client information is limited to personnel or service providers who need it for approved business purposes.
                        </p>

                        <h2>8. Data Retention</h2>
                        <p>
                            We retain information only for as long as reasonably necessary for service delivery, client support, business records, accounting, legal compliance, dispute resolution, and legitimate operational purposes. Where information is no longer required, it may be securely deleted, archived, or anonymized.
                        </p>

                        <h2>9. Client Rights and Requests</h2>
                        <p>
                            Clients may request access, correction, update, or deletion of their personal information, subject to identity verification and any applicable legal or business record-keeping requirements. Requests can be sent through the contact details available on our website.
                        </p>

                        <h2>10. Accuracy of Information</h2>
                        <p>
                            Clients are responsible for providing accurate contact details, artwork, approval instructions, delivery information, and order specifications. Nai Prints may request clarification where information is incomplete, inconsistent, or unclear.
                        </p>

                        <h2>11. Website Forms and Communications</h2>
                        <p>
                            Information submitted through website forms, email, WhatsApp, phone, or other communication channels may be used to respond to enquiries, prepare quotes, process orders, provide support, and keep service records.
                        </p>

                        <h2>12. Policy Updates</h2>
                        <p>
                            Nai Prints may update this Data Protection Policy from time to time to reflect changes in services, operations, legal requirements, or security practices. The latest version will be made available on this page.
                        </p>

                        <h2>13. Contact</h2>
                        <p>
                            For questions or data protection requests, please contact Nai Prints through the official contact channels listed on our website.
                        </p>
                    </div>
                </section>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
