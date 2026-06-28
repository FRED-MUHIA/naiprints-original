@php
    $footer = $globalFooter ?? [];
    $whatsappNumber = $footer['whatsapp_number'] ?? '254745506619';
    $whatsappMessage = $footer['whatsapp_message'] ?? 'Hello I need assistance';
    $whatsappScroll = $footer['whatsapp_scroll'] ?? true;
    $whatsappThreshold = $footer['whatsapp_threshold'] ?? 180;
    $whatsappTooltip = $footer['whatsapp_tooltip'] ?? 'Chat with us on WhatsApp';
@endphp

<div class="whatsapp-float"
     data-whatsapp-float
     data-whatsapp-number="{{ $whatsappNumber }}"
     data-whatsapp-message="{{ $whatsappMessage }}"
     data-whatsapp-scroll="{{ $whatsappScroll ? 'true' : 'false' }}"
     data-whatsapp-threshold="{{ $whatsappThreshold }}">
    <div class="whatsapp-tooltip" data-whatsapp-tooltip>
        {{ $whatsappTooltip }}
    </div>
    <a class="whatsapp-button" href="#" target="_blank" rel="noopener" aria-label="Chat on WhatsApp" data-whatsapp-link>
        <svg class="whatsapp-icon" viewBox="0 0 32 32" aria-hidden="true">
            <path fill="currentColor" d="M19.11 17.71c-.25-.12-1.49-.74-1.72-.83-.23-.08-.4-.12-.57.12-.17.24-.66.82-.81.99-.15.17-.3.19-.55.06-.25-.12-1.05-.39-2-1.24-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.38.11-.5.11-.11.25-.3.38-.44.13-.15.17-.25.25-.42.08-.17.04-.32-.02-.45-.06-.12-.57-1.38-.78-1.89-.21-.5-.42-.43-.57-.43h-.49c-.17 0-.45.06-.69.32-.24.25-.9.88-.9 2.15s.92 2.5 1.05 2.67c.13.17 1.8 2.75 4.36 3.86.61.26 1.08.42 1.45.54.61.19 1.17.16 1.61.1.49-.07 1.49-.61 1.7-1.2.21-.59.21-1.09.15-1.2-.06-.11-.23-.17-.48-.29ZM16 4.5c-6.34 0-11.5 5.16-11.5 11.5 0 2.03.54 4.02 1.56 5.78L4.5 27.5l5.85-1.54A11.46 11.46 0 0 0 16 27.5c6.34 0 11.5-5.16 11.5-11.5S22.34 4.5 16 4.5Zm0 20.96c-1.74 0-3.44-.46-4.93-1.32l-.35-.2-3.47.91.93-3.38-.23-.36a9.39 9.39 0 0 1-1.44-5.02c0-5.2 4.23-9.43 9.49-9.43 5.23 0 9.48 4.23 9.48 9.43s-4.25 9.37-9.48 9.37Z"/>
        </svg>
    </a>
</div>
