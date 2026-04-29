<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo', [
            'title' => 'Ethical Policies | Nai Prints',
            'description' => 'Read the Nai Prints ethical policy and client agreement for transparent, professional printing and branding engagements.',
            'canonical' => route('ethical-policies'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

        <main class="mx-auto max-w-4xl px-4 py-12">
            <p class="section-kicker">Ethical Policies</p>
            <h1 class="section-title">Nai Prints Corporation Ethical Policy & Client Agreement</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-700 md:text-base">
                Standards for transparency, professionalism, accountability, and ethical conduct in all client engagements.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="/documents/nai-prints-ethical-policy-client-agreement.pdf" download class="btn-brand">Download PDF</a>
                <a href="/documents/nai-prints-ethical-policy-client-agreement.docx" download class="btn-outline">Download Word</a>
            </div>

            <section class="mt-8 grid gap-6 text-sm leading-7 text-slate-700">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">Ethical Policy & Client Agreement</h2>
                    <p class="mt-3">
                        This Ethical Policy and Client Agreement sets out the principles, standards, and conditions governing all business engagements between
                        <strong>Nai Prints Corporation</strong> and its clients. It promotes transparency, professionalism, accountability, and ethical conduct.
                    </p>
                </div>

                @foreach ([
                    ['1. Purpose and scope', ['Establish ethical standards for all contracts, tenders, and service engagements.', 'Protect both Nai Prints and the Client from unethical practices, misunderstandings, or contractual disputes.', 'Ensure fairness, integrity, and professionalism throughout the service delivery process.'], 'This policy applies to all printing, branding, design, production, installation, and related services offered by Nai Prints.'],
                    ['3. Zero tolerance to kickbacks and unethical inducements', ['Requests or implies kickbacks or personal benefits.', 'Seeks unethical influence over procurement, pricing, or service allocation.', 'Encourages violation of procurement laws, tender procedures, or internal policies.'], 'Any Client found to be engaging in or proposing such practices may have their engagement terminated immediately, without liability to Nai Prints.'],
                    ['4. Handling of contract breaches', ['Notify each other in writing upon identification of a breach.', 'Allow reasonable opportunity for clarification, correction, or remedy where applicable.', 'Seek amicable resolution before pursuing legal remedies.'], 'Where resolution cannot be reached amicably, the matter may be escalated in accordance with applicable laws and dispute resolution mechanisms.'],
                    ['5. Artwork, document approval & client responsibility', ['Provide written or digital confirmation of approval.', 'Sign and return the approved artwork and/or agreement.'], 'No printing, production, or execution shall commence until Nai Prints receives the signed approval from the Client. Nai Prints shall not be responsible for approved errors, omissions, or inaccuracies.'],
                    ['6. Non-coercion and non-influence policy', ['Deliver services outside agreed timelines.', 'Provide preferential treatment or favors.', 'Bypass standard procedures or quality controls.'], 'Any attempt to influence staff for quick delivery, personal favor, or deviation from agreed terms may result in suspension or termination of services.'],
                    ['7. Ethical questioning and contractual clarification', ['Protecting both parties’ interests.', 'Ensuring compliance with this Agreement.', 'Preventing disputes or unethical practices.'], 'The Client agrees to cooperate fully in providing clarification when reasonably requested.'],
                ] as [$title, $items, $body])
                    <div class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
                        <ul class="mt-3 list-disc space-y-2 pl-5">
                            @foreach ($items as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-3">{{ $body }}</p>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">2. Reliability and contractual integrity</h2>
                    <p class="mt-3">Nai Prints is committed to honoring duly executed contracts and service agreements in good faith, according to agreed specifications, timelines, and terms.</p>
                    <p class="mt-3">The Client also undertakes to act in good faith and acknowledges responsibility for compliance with contractual obligations once an agreement is executed.</p>
                    <p class="mt-3">Any contract violation shall be addressed through responsible, lawful, and professional means.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">8. Confidentiality and professional conduct</h2>
                    <p class="mt-3">All client information, designs, and documents shall be handled with confidentiality and professionalism. Both parties agree to maintain respectful communication and ethical conduct throughout the engagement.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">9. Governing law</h2>
                    <p class="mt-3">This Agreement shall be governed and interpreted in accordance with the laws of the Republic of Kenya, unless otherwise agreed in writing.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-xl font-bold text-slate-900">10. Acceptance and binding effect</h2>
                    <p class="mt-3">By signing, both parties confirm that they have read and understood this Ethical Policy & Client Agreement, agree to be bound by it, and commit to ethical, transparent, and professional engagement.</p>
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
