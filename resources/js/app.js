import './bootstrap';
import './designer';
import { mountJerseyDesigner } from './jersey-3d-designer';

document.addEventListener('DOMContentLoaded', () => {
    mountJerseyDesigner();

    const shouldPlayOpeningWipe = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (shouldPlayOpeningWipe) {
        document.body.classList.add('is-glossy-wipe-active');
        window.setTimeout(() => {
            document.body.classList.remove('is-glossy-wipe-active');
        }, 1700);
    }

    const processedImages = new WeakSet();
    let eagerBudget = 4;

    const setIntrinsicDimensions = (img) => {
        if (
            img.hasAttribute('width') &&
            img.hasAttribute('height')
        ) {
            return;
        }

        const applyDimensions = () => {
            if (!img.naturalWidth || !img.naturalHeight) {
                return;
            }
            if (!img.hasAttribute('width')) {
                img.setAttribute('width', String(img.naturalWidth));
            }
            if (!img.hasAttribute('height')) {
                img.setAttribute('height', String(img.naturalHeight));
            }
        };

        if (img.complete) {
            applyDimensions();
            return;
        }

        img.addEventListener('load', applyDimensions, { once: true });
    };

    const shouldPrioritizeImage = (img) => {
        if (img.closest('header')) {
            return true;
        }

        const rect = img.getBoundingClientRect();
        const viewportHeight = window.innerHeight || 900;
        const aboveFoldThreshold = viewportHeight * 1.2;

        return rect.top < aboveFoldThreshold && rect.bottom > -120;
    };

    const optimizeImage = (img) => {
        if (!(img instanceof HTMLImageElement) || processedImages.has(img)) {
            return;
        }

        processedImages.add(img);
        setIntrinsicDimensions(img);

        if (!img.hasAttribute('decoding')) {
            img.decoding = 'async';
        }

        const canPrioritize = shouldPrioritizeImage(img) && eagerBudget > 0;

        if (!img.hasAttribute('fetchpriority')) {
            img.fetchPriority = canPrioritize ? 'high' : 'low';
        }

        if (!img.hasAttribute('loading')) {
            img.loading = canPrioritize ? 'eager' : 'lazy';
        }

        if (canPrioritize) {
            eagerBudget -= 1;
        }
    };

    document.querySelectorAll('img').forEach(optimizeImage);

    const imageObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node instanceof HTMLImageElement) {
                    optimizeImage(node);
                    return;
                }

                if (node instanceof HTMLElement) {
                    node.querySelectorAll('img').forEach(optimizeImage);
                }
            });
        });
    });

    imageObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });

    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileToggle = document.querySelector('[data-mobile-menu-toggle]');

    if (mobileMenu && mobileToggle) {
        const closeMenu = () => {
            mobileMenu.classList.remove('is-open');
            mobileToggle.setAttribute('aria-expanded', 'false');
        };

        const openMenu = () => {
            mobileMenu.classList.add('is-open');
            mobileToggle.setAttribute('aria-expanded', 'true');
        };

        const toggleMenu = () => {
            if (mobileMenu.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        };

        mobileToggle.addEventListener('click', (event) => {
            event.preventDefault();
            toggleMenu();
        });

        document.addEventListener('click', (event) => {
            if (
                mobileMenu.classList.contains('is-open') &&
                !mobileMenu.contains(event.target) &&
                !mobileToggle.contains(event.target)
            ) {
                closeMenu();
            }
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => closeMenu());
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeMenu();
            }
        });
    }

    const searchRoot = document.querySelector('[data-site-search]');
    const searchToggles = document.querySelectorAll('[data-site-search-toggle]');

    if (searchRoot && searchToggles.length) {
        const searchBackdrop = searchRoot.querySelector('[data-site-search-backdrop]');
        const searchCloseBtn = searchRoot.querySelector('[data-site-search-close]');
        const searchInput = searchRoot.querySelector('[data-site-search-input]');
        const searchResults = searchRoot.querySelector('[data-site-search-results]');
        const searchEmpty = searchRoot.querySelector('[data-site-search-empty]');
        const searchAllBtn = searchRoot.querySelector('[data-site-search-all]');
        const sourceNode = searchRoot.querySelector('[data-site-search-source]');

        let allEntries = [];
        try {
            const source = String(sourceNode?.textContent || '[]');
            const parsed = JSON.parse(source);
            if (Array.isArray(parsed)) {
                allEntries = parsed
                    .map((entry) => ({
                        title: String(entry?.title || '').trim(),
                        subtitle: String(entry?.subtitle || '').trim(),
                        url: String(entry?.url || '').trim(),
                        type: String(entry?.type || 'Page').trim(),
                    }))
                    .filter((entry) => entry.title && entry.url);
            }
        } catch (error) {
            allEntries = [];
        }

        const normalize = (value) =>
            String(value || '')
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

        const scoreEntry = (entry, query) => {
            const title = normalize(entry.title);
            const subtitle = normalize(entry.subtitle);
            if (!query) {
                return 1;
            }
            let score = 0;
            if (title.startsWith(query)) {
                score += 130;
            }
            if (title.includes(query)) {
                score += 80;
            }
            if (subtitle.includes(query)) {
                score += 28;
            }
            const tokens = query.split(' ').filter(Boolean);
            if (tokens.length) {
                const allTokensInTitle = tokens.every((token) => title.includes(token));
                if (allTokensInTitle) {
                    score += 36;
                }
            }
            if ((entry.type || '').toLowerCase() === 'product') {
                score += 2;
            }
            return score;
        };

        const renderEntries = (entries) => {
            if (!searchResults) {
                return;
            }
            searchResults.innerHTML = '';
            entries.forEach((entry) => {
                const li = document.createElement('li');
                const link = document.createElement('a');
                link.className = 'site-search-item';
                link.href = entry.url;

                const title = document.createElement('span');
                title.className = 'site-search-item-title';
                title.textContent = entry.title;

                const meta = document.createElement('span');
                meta.className = 'site-search-item-meta';
                meta.textContent = entry.subtitle ? `${entry.type} • ${entry.subtitle}` : entry.type;

                link.appendChild(title);
                link.appendChild(meta);
                li.appendChild(link);
                searchResults.appendChild(li);
            });

            if (searchEmpty) {
                searchEmpty.hidden = entries.length > 0;
            }
        };

        const findMatches = (rawQuery, showAll = false) => {
            const query = normalize(rawQuery);
            if (!query) {
                return showAll ? allEntries : allEntries.slice(0, 14);
            }

            const matches = allEntries
                .map((entry) => ({ entry, score: scoreEntry(entry, query) }))
                .filter((item) => item.score > 0)
                .sort((a, b) => {
                    if (b.score !== a.score) {
                        return b.score - a.score;
                    }
                    return a.entry.title.localeCompare(b.entry.title);
                });

            return (showAll ? matches : matches.slice(0, 18)).map((item) => item.entry);
        };

        const closeSearch = () => {
            searchRoot.hidden = true;
            document.body.classList.remove('site-search-open');
        };

        const openSearch = () => {
            searchRoot.hidden = false;
            document.body.classList.add('site-search-open');
            renderEntries(findMatches(searchInput?.value || ''));
            window.requestAnimationFrame(() => {
                searchInput?.focus();
                searchInput?.select();
            });
        };

        searchToggles.forEach((searchToggle) => {
            searchToggle.addEventListener('click', (event) => {
                event.preventDefault();
                openSearch();
            });
        });

        searchCloseBtn?.addEventListener('click', closeSearch);
        searchBackdrop?.addEventListener('click', closeSearch);

        searchInput?.addEventListener('input', () => {
            renderEntries(findMatches(searchInput.value));
        });

        searchAllBtn?.addEventListener('click', () => {
            renderEntries(findMatches(searchInput?.value || '', true));
        });

        searchRoot.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.closest('.site-search-item')) {
                closeSearch();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (searchRoot.hidden) {
                return;
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                closeSearch();
            }
        });
    }

    const bindClientAutofill = (scope) => {
        const select = scope.querySelector('[data-client-select]');
        if (!select) {
            return;
        }

        const fields = {
            name: scope.querySelector('[data-client-field="name"]'),
            company_name: scope.querySelector('[data-client-field="company_name"]'),
            phone: scope.querySelector('[data-client-field="phone"]'),
            email: scope.querySelector('[data-client-field="email"]'),
            address: scope.querySelector('[data-client-field="address"]'),
        };

        const datasetMap = {
            name: 'clientName',
            company_name: 'clientCompanyName',
            phone: 'clientPhone',
            email: 'clientEmail',
            address: 'clientAddress',
        };

        const applyClient = (clearWhenEmpty = true) => {
            const selected = select.options[select.selectedIndex];
            const hasClient = Boolean(select.value);

            Object.entries(fields).forEach(([key, input]) => {
                if (!input) {
                    return;
                }

                if (!hasClient) {
                    if (clearWhenEmpty) {
                        input.value = '';
                    }
                    return;
                }

                const datasetKey = datasetMap[key];
                input.value = selected?.dataset?.[datasetKey] ?? '';
            });
        };

        select.addEventListener('change', () => applyClient(true));

        const hasTypedValues = Object.values(fields).some((input) => (input?.value || '').trim() !== '');
        if (select.value && !hasTypedValues) {
            applyClient(false);
        }
    };

    document.querySelectorAll('[data-client-autofill]').forEach(bindClientAutofill);

    const invoiceBuilder = document.querySelector('[data-invoice-builder]');
    if (invoiceBuilder) {
        const itemsBody = invoiceBuilder.querySelector('[data-invoice-items]');
        const template = invoiceBuilder.querySelector('[data-invoice-template]');
        const addButton = invoiceBuilder.querySelector('[data-invoice-add]');
        const subtotalEl = invoiceBuilder.querySelector('[data-invoice-subtotal]');
        const taxAmountEl = invoiceBuilder.querySelector('[data-invoice-tax-amount]');
        const grandTotalEl = invoiceBuilder.querySelector('[data-invoice-grand-total]');
        const taxRateInput = invoiceBuilder.querySelector('[data-invoice-tax-rate]');
        const taxToggle = invoiceBuilder.querySelector('[data-invoice-tax-toggle]');
        const discountInput = invoiceBuilder.querySelector('[data-invoice-discount]');

        const formatMoney = (value) => `KES ${value.toFixed(2)}`;

        const readNumber = (input) => {
            const value = Number.parseFloat(input?.value || '0');
            return Number.isNaN(value) ? 0 : value;
        };

        const updateRow = (row) => {
            const qtyInput = row.querySelector('[data-invoice-qty]');
            const priceInput = row.querySelector('[data-invoice-price]');
            const totalInput = row.querySelector('[data-invoice-total]');
            const qty = readNumber(qtyInput);
            const price = readNumber(priceInput);
            const total = Math.max(0, qty * price);
            if (totalInput) {
                totalInput.value = formatMoney(total);
            }
            row.dataset.rowTotal = String(total);
        };

        const updateTotals = () => {
            const rows = itemsBody?.querySelectorAll('[data-invoice-row]') ?? [];
            let subtotal = 0;
            rows.forEach((row) => {
                updateRow(row);
                subtotal += Number.parseFloat(row.dataset.rowTotal || '0');
            });

            const discount = Math.max(0, readNumber(discountInput));
            const taxable = Math.max(0, subtotal - discount);
            const taxRate = taxToggle?.checked ? Math.max(0, readNumber(taxRateInput)) : 0;
            const taxAmount = taxable * (taxRate / 100);
            const grandTotal = taxable + taxAmount;

            if (subtotalEl) subtotalEl.textContent = formatMoney(subtotal);
            if (taxAmountEl) taxAmountEl.textContent = formatMoney(taxAmount);
            if (grandTotalEl) grandTotalEl.textContent = formatMoney(grandTotal);
        };

        const bindRow = (row) => {
            row.querySelectorAll('[data-invoice-qty], [data-invoice-price]').forEach((input) => {
                input.addEventListener('input', updateTotals);
            });
            row.querySelector('[data-invoice-remove]')?.addEventListener('click', () => {
                row.remove();
                updateTotals();
            });
        };

        itemsBody?.querySelectorAll('[data-invoice-row]').forEach(bindRow);

        addButton?.addEventListener('click', () => {
            if (!template || !itemsBody) {
                return;
            }
            const nextIndex = itemsBody.querySelectorAll('[data-invoice-row]').length;
            const markup = template.innerHTML.replaceAll('__INDEX__', String(nextIndex)).trim();
            if (!markup) {
                return;
            }
            const wrapper = document.createElement('template');
            wrapper.innerHTML = markup;
            const row = wrapper.content.firstElementChild;
            if (!row) {
                return;
            }
            itemsBody.appendChild(row);
            bindRow(row);
            updateTotals();
        });

        if (taxToggle && taxRateInput) {
            taxRateInput.toggleAttribute('disabled', !taxToggle.checked);
        }

        taxToggle?.addEventListener('change', () => {
            if (taxRateInput) {
                taxRateInput.toggleAttribute('disabled', !taxToggle.checked);
            }
            updateTotals();
        });
        taxRateInput?.addEventListener('input', updateTotals);
        discountInput?.addEventListener('input', updateTotals);

        updateTotals();
    }

    document.querySelectorAll('[data-category-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const item = track?.querySelector('[data-carousel-item]');

        if (!track || !item) {
            return;
        }

        const gap = parseFloat(getComputedStyle(track).gap || '0');
        const step = item.getBoundingClientRect().width + gap;

        const scrollBy = (direction) => {
            const maxScroll = track.scrollWidth - track.clientWidth;
            if (direction > 0 && track.scrollLeft + step >= maxScroll) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }

            track.scrollBy({ left: step * direction, behavior: 'smooth' });
        };

        prev?.addEventListener('click', () => scrollBy(-1));
        next?.addEventListener('click', () => scrollBy(1));

        let isDragging = false;
        let startX = 0;
        let startScroll = 0;

        const startDrag = (event) => {
            isDragging = true;
            startX = event.pageX ?? event.touches?.[0]?.pageX ?? 0;
            startScroll = track.scrollLeft;
            track.classList.add('is-dragging');
        };

        const moveDrag = (event) => {
            if (!isDragging) {
                return;
            }
            const currentX = event.pageX ?? event.touches?.[0]?.pageX ?? 0;
            const delta = currentX - startX;
            track.scrollLeft = startScroll - delta;
        };

        const stopDrag = () => {
            isDragging = false;
            track.classList.remove('is-dragging');
        };

        track.addEventListener('mousedown', startDrag);
        track.addEventListener('touchstart', startDrag, { passive: true });
        window.addEventListener('mousemove', moveDrag);
        window.addEventListener('touchmove', moveDrag, { passive: true });
        window.addEventListener('mouseup', stopDrag);
        window.addEventListener('touchend', stopDrag);

        let timer = null;
        const hasOverflow = () => track.scrollWidth > track.clientWidth + 2;
        const startAuto = () => {
            if (timer) {
                return;
            }
            if (!hasOverflow()) {
                return;
            }
            timer = setInterval(() => scrollBy(1), 4000);
        };

        const stopAuto = () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        carousel.addEventListener('mouseenter', stopAuto);
        carousel.addEventListener('mouseleave', startAuto);
        carousel.addEventListener('focusin', stopAuto);
        carousel.addEventListener('focusout', startAuto);

        startAuto();

        window.addEventListener('resize', () => {
            if (!hasOverflow()) {
                stopAuto();
            } else {
                startAuto();
            }
        });
    });

    document.querySelectorAll('[data-related-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-related-track]');
        const prev = carousel.querySelector('[data-related-prev]');
        const next = carousel.querySelector('[data-related-next]');
        const item = track?.querySelector('[data-related-item]');

        if (!track || !item) {
            return;
        }

        const gap = parseFloat(getComputedStyle(track).gap || '0');
        const step = item.getBoundingClientRect().width + gap;

        const scrollBy = (direction) => {
            const maxScroll = track.scrollWidth - track.clientWidth;
            if (direction > 0 && track.scrollLeft + step >= maxScroll) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }

            track.scrollBy({ left: step * direction, behavior: 'smooth' });
        };

        prev?.addEventListener('click', () => scrollBy(-1));
        next?.addEventListener('click', () => scrollBy(1));
    });

    const CART_KEY = 'nai_cart';
    const WISHLIST_KEY = 'nai_wishlist';

    const readCart = () => {
        try {
            const stored = localStorage.getItem(CART_KEY);
            return stored ? JSON.parse(stored) : [];
        } catch (error) {
            return [];
        }
    };

    const writeCart = (items) => {
        try {
            localStorage.setItem(CART_KEY, JSON.stringify(items));
        } catch (error) {
            // Ignore storage errors.
        }
    };

    const getCartCount = (items) => items.reduce((total, item) => total + (item.qty || 0), 0);

    const updateCartCount = () => {
        const count = getCartCount(readCart());
        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
            badge.textContent = count;
            badge.classList.toggle('hidden', count === 0);
            badge.classList.toggle('is-green', count > 0);
        });
    };

    const readWishlist = () => {
        try {
            const stored = localStorage.getItem(WISHLIST_KEY);
            const parsed = stored ? JSON.parse(stored) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    };

    const writeWishlist = (items) => {
        try {
            localStorage.setItem(WISHLIST_KEY, JSON.stringify(items));
        } catch (error) {
            // Ignore storage errors.
        }
    };

    const updateWishlistCount = () => {
        const count = readWishlist().length;
        document.querySelectorAll('[data-wishlist-count]').forEach((badge) => {
            badge.textContent = count;
            badge.classList.toggle('hidden', count === 0);
        });
    };

    const currencyFormatter = new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const formatMoney = (value) => {
        const amount = Number.isFinite(value) ? value : 0;
        return `KSh ${currencyFormatter.format(amount)}`;
    };

    const getCartSubtotal = (items) =>
        items.reduce((total, item) => total + (Number(item.price) || 0) * (Number(item.qty) || 0), 0);

    let renderCartPopup = null;
    let renderCheckout = null;
    let cartToastTimer = null;
    const cartToast = document.querySelector('[data-cart-toast]');
    const cartToastText = document.querySelector('[data-cart-toast-text]');

    const showCartToast = (message) => {
        if (!cartToast) {
            return;
        }
        if (cartToastText && message) {
            cartToastText.textContent = message;
        }
        cartToast.classList.add('is-visible');
        cartToast.setAttribute('aria-hidden', 'false');
        if (cartToastTimer) {
            clearTimeout(cartToastTimer);
        }
        cartToastTimer = window.setTimeout(() => {
            cartToast.classList.remove('is-visible');
            cartToast.setAttribute('aria-hidden', 'true');
        }, 2200);
    };

    const addToCart = (payload) => {
        const items = readCart();
        const quantity = Number.isFinite(payload.qty) && payload.qty > 0 ? payload.qty : 1;
        const existing = items.find((item) => item.id === payload.id);
        if (existing) {
            existing.qty += quantity;
        } else {
            items.push({ ...payload, qty: quantity });
        }
        writeCart(items);
        updateCartCount();
        if (typeof renderCartPopup === 'function') {
            renderCartPopup();
        }
        if (typeof renderCheckout === 'function') {
            renderCheckout();
        }
        showCartToast(`Added ${payload.name || 'item'} to cart.`);
    };

    document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
        button.dataset.cartBound = '1';
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const { productId, productName, productPrice, productImage, productUrl } = button.dataset;
            if (!productId) {
                return;
            }

            const price = Number.parseFloat(productPrice || '0');
            let quantity = 1;
            const panel = button.closest('[data-product-panel]');
            const selectedColor = panel?.dataset.selectedColor || '';
            const selectedSize = panel?.dataset.selectedSize || '';
            const qtyInput = panel?.querySelector('[data-qty-input]');
            if (qtyInput) {
                const parsed = Number.parseInt(qtyInput.value || '1', 10);
                quantity = Number.isNaN(parsed) || parsed < 1 ? 1 : parsed;
            }
            const variantId =
                selectedColor || selectedSize ? `${productId}::${selectedColor}::${selectedSize}` : productId;
            addToCart({
                id: variantId,
                baseId: productId,
                name: productName || 'Product',
                price: Number.isNaN(price) ? 0 : price,
                image: productImage || '',
                url: productUrl || '',
                color: selectedColor,
                size: selectedSize,
                qty: quantity,
            });

            button.classList.add('is-added');
            setTimeout(() => button.classList.remove('is-added'), 700);
        });
    });

    const normalizeWhatsAppNumber = (value) => {
        const digits = String(value || '').replace(/[^\d]/g, '');
        if (digits.startsWith('0')) {
            return `254${digits.slice(1)}`;
        }
        if (digits.length === 9 && (digits.startsWith('7') || digits.startsWith('1'))) {
            return `254${digits}`;
        }
        return digits;
    };

    const buildWhatsAppOrderMessage = (button) => {
        const panel = button.closest('[data-product-panel]');
        const qtyInput = panel?.querySelector('[data-qty-input]');
        const quantity = Math.max(1, Number.parseInt(qtyInput?.value || '1', 10) || 1);
        const price = Number.parseFloat(button.dataset.productPrice || '0') || 0;
        const selectedColor = panel?.dataset.selectedColor || '';
        const selectedSize = panel?.dataset.selectedSize || '';
        const total = price * quantity;
        const lines = [
            'Hello Nai Prints, I would like to place a WhatsApp order.',
            '',
            `Product: ${button.dataset.productName || 'Product'}`,
            button.dataset.productSku ? `SKU: ${button.dataset.productSku}` : '',
            button.dataset.productCategory ? `Category: ${button.dataset.productCategory}` : '',
            `Quantity: ${quantity}`,
            selectedColor ? `Color: ${selectedColor}` : '',
            selectedSize ? `Size: ${selectedSize}` : '',
            `Unit price: ${formatMoney(price)}`,
            `Estimated total: ${formatMoney(total)}`,
            button.dataset.productUrl ? `Product link: ${button.dataset.productUrl}` : '',
            '',
            'Please confirm availability and delivery.'
        ];

        return lines.filter((line) => line !== '').join('\n');
    };

    document.querySelectorAll('[data-whatsapp-order]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const number = normalizeWhatsAppNumber(button.dataset.whatsappNumber || '254745506619');
            if (!number) {
                return;
            }

            const message = buildWhatsAppOrderMessage(button);
            window.open(`https://wa.me/${number}?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
        });
    });

    document.querySelectorAll('[data-qty-control]').forEach((control) => {
        const input = control.querySelector('[data-qty-input]');
        const decrease = control.querySelector('[data-qty-decrease]');
        const increase = control.querySelector('[data-qty-increase]');

        if (!input) {
            return;
        }

        const setValue = (value) => {
            const nextValue = Math.max(1, value);
            input.value = String(nextValue);
        };

        decrease?.addEventListener('click', () => {
            const current = Number.parseInt(input.value || '1', 10);
            setValue(Number.isNaN(current) ? 1 : current - 1);
        });

        increase?.addEventListener('click', () => {
            const current = Number.parseInt(input.value || '1', 10);
            setValue(Number.isNaN(current) ? 1 : current + 1);
        });
    });

    document.querySelectorAll('[data-option-group]').forEach((group) => {
        const items = Array.from(group.querySelectorAll('[data-option-item]'));
        if (items.length === 0) {
            return;
        }

        const groupType = (group.dataset.optionGroup || 'option').toLowerCase();
        const panel =
            group.closest('[data-product-panel]') ||
            group.closest('[data-product-scope]')?.querySelector('[data-product-panel]');

        const getValue = (item) =>
            item.dataset.optionValue || item.dataset.color || item.textContent?.trim() || '';

        const setSelected = (item) => {
            items.forEach((btn) => btn.classList.remove('is-active'));
            item.classList.add('is-active');
            const value = getValue(item);
            if (!panel || !value) {
                return;
            }
            if (groupType === 'color') {
                panel.dataset.selectedColor = value;
            } else if (groupType === 'size') {
                panel.dataset.selectedSize = value;
            } else {
                panel.dataset.selectedOption = value;
            }
        };

        const preset = items.find((item) => item.classList.contains('is-active')) || items[0];
        if (preset) {
            setSelected(preset);
        }

        items.forEach((item) => {
            item.addEventListener('click', () => setSelected(item));
        });
    });

    document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
        const mainImage = gallery.querySelector('[data-gallery-main]');
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        const prev = gallery.querySelector('[data-gallery-prev]');
        const next = gallery.querySelector('[data-gallery-next]');

        if (!mainImage || thumbs.length === 0) {
            return;
        }

        let activeIndex = 0;

        const setActive = (index) => {
            const clamped = ((index % thumbs.length) + thumbs.length) % thumbs.length;
            const nextThumb = thumbs[clamped];
            const nextSrc = nextThumb?.dataset.gallerySrc;
            if (!nextSrc) {
                return;
            }

            activeIndex = clamped;
            mainImage.setAttribute('src', nextSrc);
            thumbs.forEach((btn) => btn.classList.remove('is-active'));
            nextThumb.classList.add('is-active');
        };

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => setActive(index));
        });

        prev?.addEventListener('click', () => setActive(activeIndex - 1));
        next?.addEventListener('click', () => setActive(activeIndex + 1));

        setActive(activeIndex);
    });

    document.querySelectorAll('[data-hero-video-tile]').forEach((tile) => {
        const video = tile.querySelector('.hero-tile-video-el');
        if (!video) {
            return;
        }

        video.play().catch(() => {});
    });

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!prefersReducedMotion.matches) {
        const tracks = document.querySelectorAll('.announcement-track');
        const updateTrack = (track) => {
            const speed = Number.parseFloat(track.dataset.announcementSpeed || '120');
            const group = track.querySelector('.announcement-group');
            if (!group || !Number.isFinite(speed) || speed <= 0) {
                return;
            }
            const distance = group.scrollWidth;
            if (!distance) {
                return;
            }
            const duration = Math.max(distance / speed, 6);
            track.style.animationDuration = `${duration}s`;
        };

        const updateAll = () => tracks.forEach(updateTrack);
        updateAll();

        let resizeTimer = null;
        window.addEventListener('resize', () => {
            if (resizeTimer) {
                clearTimeout(resizeTimer);
            }
            resizeTimer = setTimeout(updateAll, 150);
        });
    }

    document.querySelectorAll('[data-gift-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-gift-track]');
        const card = track?.querySelector('.gift-product-card');
        if (!track || !card) {
            return;
        }

        const getStep = () => {
            const gap = Number.parseFloat(getComputedStyle(track).gap || '0');
            return card.getBoundingClientRect().width + gap;
        };

        let timer = null;
        const intervalSeconds = Number.parseFloat(carousel.dataset.carouselInterval || '5');
        const intervalMs = Number.isNaN(intervalSeconds) ? 5000 : intervalSeconds * 1000;

        const autoScroll = () => {
            const maxScroll = track.scrollWidth - track.clientWidth;
            if (maxScroll <= 0) {
                return;
            }
            const next = track.scrollLeft + getStep();
            if (next >= maxScroll - 4) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }
            track.scrollBy({ left: getStep(), behavior: 'smooth' });
        };

        const start = () => {
            if (timer) {
                return;
            }
            timer = window.setInterval(autoScroll, intervalMs);
        };

        const stop = () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);

        start();
    });

    document.querySelectorAll('[data-sale-countdown]').forEach((countdown) => {
        const endAt = Date.parse(countdown.dataset.saleEndsAt || '');
        const daysNode = countdown.querySelector('[data-sale-days]');
        const hoursNode = countdown.querySelector('[data-sale-hours]');
        const minutesNode = countdown.querySelector('[data-sale-minutes]');
        const secondsNode = countdown.querySelector('[data-sale-seconds]');

        if (
            Number.isNaN(endAt) ||
            !daysNode ||
            !hoursNode ||
            !minutesNode ||
            !secondsNode
        ) {
            return;
        }

        const pad = (value) => String(Math.max(0, value)).padStart(2, '0');
        let timer = null;

        const update = () => {
            const totalSeconds = Math.max(0, Math.floor((endAt - Date.now()) / 1000));
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            daysNode.textContent = pad(days);
            hoursNode.textContent = pad(hours);
            minutesNode.textContent = pad(minutes);
            secondsNode.textContent = pad(seconds);

            if (totalSeconds <= 0 && timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        update();
        timer = window.setInterval(update, 1000);
    });

    document.querySelectorAll('[data-gift-category-select]').forEach((select) => {
        const fallbackUrl = (select.dataset.giftFallbackUrl || '/shop').trim();

        select.addEventListener('change', () => {
            const selectedUrl = (select.value || fallbackUrl).trim();
            if (!selectedUrl || selectedUrl === '#') {
                return;
            }
            window.location.assign(selectedUrl);
        });
    });

    document.querySelectorAll('[data-announcement-builder]').forEach((builder) => {
        const list = builder.querySelector('[data-announcement-list]');
        const addButton = builder.querySelector('[data-announcement-add]');
        const template = builder.querySelector('[data-announcement-template]');

        if (!list || !addButton || !template) {
            return;
        }

        const getNextIndex = () => {
            let maxIndex = -1;
            list.querySelectorAll('[data-announcement-row]').forEach((row) => {
                const parsed = Number.parseInt(row.dataset.index || '0', 10);
                if (!Number.isNaN(parsed)) {
                    maxIndex = Math.max(maxIndex, parsed);
                }
            });
            return maxIndex + 1;
        };

        addButton.addEventListener('click', () => {
            const nextIndex = getNextIndex();
            const markup = template.innerHTML.replaceAll('__INDEX__', String(nextIndex)).trim();
            if (!markup) {
                return;
            }
            const wrapper = document.createElement('template');
            wrapper.innerHTML = markup;
            const node = wrapper.content.firstElementChild;
            if (!node) {
                return;
            }
            node.dataset.index = String(nextIndex);
            list.appendChild(node);
            const input = node.querySelector('input[type="text"]');
            input?.focus();
        });
    });

    const initRepeater = (builderSelector, config) => {
        document.querySelectorAll(builderSelector).forEach((builder) => {
            const list = builder.querySelector(config.list);
            const addButton = builder.querySelector(config.add);
            const template = builder.querySelector(config.template);

            if (!list || !addButton || !template) {
                return;
            }

            const getNextIndex = () => {
                let maxIndex = -1;
                list.querySelectorAll(config.row).forEach((row) => {
                    const parsed = Number.parseInt(row.dataset.index || '0', 10);
                    if (!Number.isNaN(parsed)) {
                        maxIndex = Math.max(maxIndex, parsed);
                    }
                });
                return maxIndex + 1;
            };

            const bindRemove = (row) => {
                const remove = row.querySelector(config.remove);
                remove?.addEventListener('click', () => row.remove());
            };

            list.querySelectorAll(config.row).forEach(bindRemove);

            addButton.addEventListener('click', () => {
                const nextIndex = getNextIndex();
                const markup = template.innerHTML.replaceAll('__INDEX__', String(nextIndex)).trim();
                if (!markup) {
                    return;
                }
                const wrapper = document.createElement('template');
                wrapper.innerHTML = markup;
                const node = wrapper.content.firstElementChild;
                if (!node) {
                    return;
                }
                node.dataset.index = String(nextIndex);
                list.appendChild(node);
                bindRemove(node);
                const input = node.querySelector('input[type="text"]');
                input?.focus();
            });
        });
    };

    initRepeater('[data-menu-builder]', {
        list: '[data-menu-list]',
        add: '[data-menu-add]',
        template: '[data-menu-template]',
        row: '[data-menu-row]',
        remove: '[data-menu-remove]',
    });

    initRepeater('[data-strip-builder]', {
        list: '[data-strip-list]',
        add: '[data-strip-add]',
        template: '[data-strip-template]',
        row: '[data-strip-row]',
        remove: '[data-strip-remove]',
    });

    const initFooterBuilder = () => {
        document.querySelectorAll('[data-footer-builder]').forEach((builder) => {
            const columnsList = builder.querySelector('[data-footer-columns]');
            const addColumnButton = builder.querySelector('[data-footer-column-add]');
            const columnTemplate = builder.querySelector('[data-footer-column-template]');

            if (!columnsList || !addColumnButton || !columnTemplate) {
                return;
            }

            const getNextColumnIndex = () => {
                let maxIndex = -1;
                columnsList.querySelectorAll('[data-footer-column]').forEach((column) => {
                    const parsed = Number.parseInt(column.dataset.index || '0', 10);
                    if (!Number.isNaN(parsed)) {
                        maxIndex = Math.max(maxIndex, parsed);
                    }
                });
                return maxIndex + 1;
            };

            const bindLinkRow = (row) => {
                const remove = row.querySelector('[data-footer-link-remove]');
                remove?.addEventListener('click', () => row.remove());
            };

            const bindColumn = (column) => {
                const remove = column.querySelector('[data-footer-column-remove]');
                remove?.addEventListener('click', () => column.remove());

                const linksList = column.querySelector('[data-footer-links]');
                const addLinkButton = column.querySelector('[data-footer-link-add]');
                const linkTemplate = column.querySelector('[data-footer-link-template]');

                if (!linksList || !addLinkButton || !linkTemplate) {
                    return;
                }

                const getNextLinkIndex = () => {
                    let maxIndex = -1;
                    linksList.querySelectorAll('[data-footer-link]').forEach((row) => {
                        const parsed = Number.parseInt(row.dataset.index || '0', 10);
                        if (!Number.isNaN(parsed)) {
                            maxIndex = Math.max(maxIndex, parsed);
                        }
                    });
                    return maxIndex + 1;
                };

                linksList.querySelectorAll('[data-footer-link]').forEach(bindLinkRow);

                addLinkButton.addEventListener('click', () => {
                    const nextIndex = getNextLinkIndex();
                    const colIndex = column.dataset.index || '0';
                    const markup = linkTemplate.innerHTML
                        .replaceAll('__COL_INDEX__', String(colIndex))
                        .replaceAll('__LINK_INDEX__', String(nextIndex))
                        .trim();
                    if (!markup) {
                        return;
                    }
                    const wrapper = document.createElement('template');
                    wrapper.innerHTML = markup;
                    const node = wrapper.content.firstElementChild;
                    if (!node) {
                        return;
                    }
                    node.dataset.index = String(nextIndex);
                    linksList.appendChild(node);
                    bindLinkRow(node);
                    const input = node.querySelector('input[type="text"]');
                    input?.focus();
                });
            };

            columnsList.querySelectorAll('[data-footer-column]').forEach(bindColumn);

            addColumnButton.addEventListener('click', () => {
                const nextIndex = getNextColumnIndex();
                const markup = columnTemplate.innerHTML.replaceAll('__COL_INDEX__', String(nextIndex)).trim();
                if (!markup) {
                    return;
                }
                const wrapper = document.createElement('template');
                wrapper.innerHTML = markup;
                const node = wrapper.content.firstElementChild;
                if (!node) {
                    return;
                }
                node.dataset.index = String(nextIndex);
                columnsList.appendChild(node);
                bindColumn(node);
                const input = node.querySelector('input[type="text"]');
                input?.focus();
            });
        });
    };

    initFooterBuilder();

    initRepeater('[data-purchase-popup-builder]', {
        list: '[data-purchase-popup-list]',
        add: '[data-purchase-popup-add]',
        template: '[data-purchase-popup-template]',
        row: '[data-purchase-popup-row]',
        remove: '[data-purchase-popup-remove]',
    });

    document.querySelectorAll('[data-purchase-popup-builder]').forEach((builder) => {
        builder.addEventListener('change', (event) => {
            const select = event.target.closest('[data-purchase-popup-product]');
            if (!select) {
                return;
            }
            const row = select.closest('[data-purchase-popup-row]');
            const productInput = row?.querySelector('[data-purchase-popup-product-name]');
            if (!productInput) {
                return;
            }
            const selectedLabel = select.options[select.selectedIndex]?.textContent?.trim() || '';
            if (select.value && selectedLabel) {
                productInput.value = selectedLabel;
            }
        });
    });

    document.querySelectorAll('[data-purchase-popup]').forEach((popup) => {
        const items = JSON.parse(popup.dataset.purchaseItems || '[]');
        if (!Array.isArray(items) || items.length === 0) {
            return;
        }

        const intervalSeconds = Number.parseInt(popup.dataset.purchaseInterval || '12', 10);
        const intervalMs = Math.max(6000, (Number.isNaN(intervalSeconds) ? 12 : intervalSeconds) * 1000);
        const visibleMs = Math.min(intervalMs - 1000, Math.max(3500, Math.floor(intervalMs * 0.7)));
        const hiddenMs = Math.max(800, intervalMs - visibleMs);

        const nameEl = popup.querySelector('[data-purchase-name]');
        const productEl = popup.querySelector('[data-purchase-product]');
        const timeEl = popup.querySelector('[data-purchase-time]');
        const imageEl = popup.querySelector('[data-purchase-image]');
        const fallbackEl = popup.querySelector('[data-purchase-fallback]');
        const linkEl = popup.querySelector('[data-purchase-link]');
        const closeEl = popup.querySelector('[data-purchase-close]');

        let index = Math.floor(Math.random() * items.length);
        let timer = null;

        const setImage = (src) => {
            const hasImage = Boolean(src);
            if (imageEl) {
                imageEl.src = src || '';
                imageEl.hidden = !hasImage;
            }
            if (fallbackEl) {
                fallbackEl.hidden = hasImage;
            }
        };

        const setLink = (url) => {
            if (!linkEl) {
                return;
            }
            const safeUrl = url || '#';
            linkEl.setAttribute('href', safeUrl);
            linkEl.setAttribute('aria-label', safeUrl === '#' ? 'Product image' : `View ${productEl?.textContent || 'product'}`);
            linkEl.classList.toggle('is-disabled', safeUrl === '#');
        };

        const applyItem = (item) => {
            if (!item || typeof item !== 'object') {
                return;
            }
            if (nameEl) nameEl.textContent = item.name || 'Someone';
            if (productEl) productEl.textContent = item.product || '';
            if (timeEl) timeEl.textContent = item.time || '';
            setImage(item.image || '');
            setLink(item.url || '');
        };

        const showPopup = () => {
            popup.classList.add('is-visible');
        };

        const hidePopup = () => {
            popup.classList.remove('is-visible');
        };

        const getNextIndex = () => {
            if (items.length <= 1) {
                return index;
            }
            let next = Math.floor(Math.random() * items.length);
            while (next === index) {
                next = Math.floor(Math.random() * items.length);
            }
            return next;
        };

        const scheduleCycle = () => {
            clearTimeout(timer);
            showPopup();
            timer = window.setTimeout(() => {
                hidePopup();
                timer = window.setTimeout(() => {
                    index = getNextIndex();
                    applyItem(items[index]);
                    scheduleCycle();
                }, hiddenMs);
            }, visibleMs);
        };

        applyItem(items[index]);
        timer = window.setTimeout(() => {
            scheduleCycle();
        }, 90000);

        const closePopup = (event) => {
            event?.preventDefault();
            event?.stopPropagation();
            hidePopup();
            clearTimeout(timer);
            timer = window.setTimeout(() => {
                scheduleCycle();
            }, hiddenMs);
        };

        closeEl?.addEventListener('pointerdown', closePopup);
        closeEl?.addEventListener('click', closePopup);
    });

    const bindCartItemControls = (container) => {
        if (!container) {
            return;
        }

        container.addEventListener('click', (event) => {
            const actionButton = event.target.closest('[data-cart-action]');
            if (!actionButton) {
                return;
            }

            const row = actionButton.closest('[data-cart-item]');
            const cartId = row?.dataset.cartId;
            if (!cartId) {
                return;
            }

            const items = readCart();
            const target = items.find((item) => item.id === cartId);
            if (!target) {
                return;
            }

            const action = actionButton.dataset.cartAction;
            if (action === 'increase') {
                target.qty = (target.qty || 1) + 1;
            } else if (action === 'decrease') {
                target.qty = Math.max(1, (target.qty || 1) - 1);
            } else if (action === 'remove') {
                const next = items.filter((item) => item.id !== cartId);
                writeCart(next);
                updateCartCount();
                if (typeof renderCartPopup === 'function') {
                    renderCartPopup();
                }
                if (typeof renderCheckout === 'function') {
                    renderCheckout();
                }
                return;
            }

            writeCart(items);
            updateCartCount();
            if (typeof renderCartPopup === 'function') {
                renderCartPopup();
            }
            if (typeof renderCheckout === 'function') {
                renderCheckout();
            }
        });
    };

    const buildCartItem = (item) => {
        const row = document.createElement('li');
        row.className = 'cart-item';
        row.dataset.cartItem = '';
        row.dataset.cartId = item.id || '';

        const media = document.createElement('div');
        media.className = 'cart-item-media';

        if (item.image) {
            const image = document.createElement('img');
            image.src = item.image;
            image.alt = item.name || 'Product';
            media.appendChild(image);
        } else {
            media.textContent = (item.name || 'Item').slice(0, 2).toUpperCase();
        }

        const info = document.createElement('div');
        info.className = 'cart-item-info';

        const name = document.createElement('a');
        name.className = 'cart-item-name';
        name.textContent = item.name || 'Product';
        name.href = item.url || '#';

        const variantParts = [item.color, item.size].filter(Boolean);
        const variant = document.createElement('p');
        variant.className = 'cart-item-variant';
        variant.textContent = variantParts.length > 0 ? variantParts.join(' / ') : 'Standard';

        const meta = document.createElement('div');
        meta.className = 'cart-item-meta';

        const qty = document.createElement('div');
        qty.className = 'cart-qty';

        const decrease = document.createElement('button');
        decrease.type = 'button';
        decrease.dataset.cartAction = 'decrease';
        decrease.setAttribute('aria-label', 'Decrease quantity');
        decrease.textContent = '-';

        const qtyValue = document.createElement('span');
        qtyValue.dataset.cartQty = '';
        qtyValue.textContent = String(item.qty || 1);

        const increase = document.createElement('button');
        increase.type = 'button';
        increase.dataset.cartAction = 'increase';
        increase.setAttribute('aria-label', 'Increase quantity');
        increase.textContent = '+';

        qty.append(decrease, qtyValue, increase);

        const price = document.createElement('span');
        price.className = 'cart-item-price';
        price.textContent = formatMoney((Number(item.price) || 0) * (Number(item.qty) || 0));

        meta.append(qty, price);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'cart-remove';
        remove.dataset.cartAction = 'remove';
        remove.textContent = 'Remove';

        info.append(name, variant, meta, remove);
        row.append(media, info);

        return row;
    };

    const cartPopup = document.querySelector('[data-cart-popup]');
    if (cartPopup) {
        const itemsList = cartPopup.querySelector('[data-cart-items]');
        const emptyState = cartPopup.querySelector('[data-cart-empty]');
        const subtotalEl = cartPopup.querySelector('[data-cart-subtotal]');
        const totalEl = cartPopup.querySelector('[data-cart-total]');

        renderCartPopup = () => {
            const items = readCart();
            if (itemsList) {
                itemsList.innerHTML = '';
                items.forEach((item) => itemsList.appendChild(buildCartItem(item)));
                itemsList.classList.toggle('hidden', items.length === 0);
            }
            if (emptyState) {
                emptyState.classList.toggle('hidden', items.length > 0);
            }
            const subtotal = getCartSubtotal(items);
            if (subtotalEl) subtotalEl.textContent = formatMoney(subtotal);
            if (totalEl) totalEl.textContent = formatMoney(subtotal);
        };

        const openCart = () => {
            renderCartPopup();
            cartPopup.classList.add('is-visible');
            cartPopup.setAttribute('aria-hidden', 'false');
            document.body.classList.add('cart-popup-open');
        };

        const closeCart = () => {
            cartPopup.classList.remove('is-visible');
            cartPopup.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('cart-popup-open');
        };

        document.querySelectorAll('[data-cart-button]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                openCart();
            });
        });

        cartPopup.querySelectorAll('[data-cart-close]').forEach((button) => {
            button.addEventListener('click', closeCart);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && cartPopup.classList.contains('is-visible')) {
                closeCart();
            }
        });

        bindCartItemControls(itemsList);
        renderCartPopup();
    }

    const checkoutPage = document.querySelector('[data-checkout-page]');
    if (checkoutPage) {
        const checkoutItems = checkoutPage.querySelector('[data-checkout-items]');
        const checkoutEmpty = checkoutPage.querySelector('[data-checkout-empty]');
        const checkoutSubtotal = checkoutPage.querySelector('[data-checkout-subtotal]');
        const checkoutTotal = checkoutPage.querySelector('[data-checkout-total]');
        const checkoutSubmit = checkoutPage.querySelector('[data-checkout-submit]');
        const checkoutForm = checkoutPage.querySelector('[data-checkout-form]');
        const checkoutItemsField = checkoutPage.querySelector('[data-checkout-items-field]');
        const checkoutSubtotalField = checkoutPage.querySelector('[data-checkout-subtotal-field]');
        const checkoutTotalField = checkoutPage.querySelector('[data-checkout-total-field]');
        const paymentInputs = checkoutPage.querySelectorAll('input[name="payment"]');

        renderCheckout = () => {
            const items = readCart();
            if (checkoutItems) {
                checkoutItems.innerHTML = '';
                items.forEach((item) => checkoutItems.appendChild(buildCartItem(item)));
                checkoutItems.classList.toggle('hidden', items.length === 0);
            }
            if (checkoutEmpty) {
                checkoutEmpty.classList.toggle('hidden', items.length > 0);
            }
            const subtotal = getCartSubtotal(items);
            if (checkoutSubtotal) checkoutSubtotal.textContent = formatMoney(subtotal);
            if (checkoutTotal) checkoutTotal.textContent = formatMoney(subtotal);
            if (checkoutSubmit) {
                checkoutSubmit.toggleAttribute('disabled', items.length === 0);
            }
            if (checkoutItemsField) {
                checkoutItemsField.value = JSON.stringify(
                    items.map((item) => ({
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        qty: item.qty,
                        image: item.image,
                        url: item.url,
                        color: item.color,
                        size: item.size,
                    }))
                );
            }
            if (checkoutSubtotalField) {
                checkoutSubtotalField.value = subtotal.toFixed(2);
            }
            if (checkoutTotalField) {
                checkoutTotalField.value = subtotal.toFixed(2);
            }
        };

        const updatePaymentButton = () => {
            if (!checkoutSubmit) return;
            const selected = checkoutPage.querySelector('input[name="payment"]:checked')?.value;
            if (selected === 'mobile_money') {
                checkoutSubmit.textContent = 'Pay with M-Pesa';
                checkoutSubmit.classList.add('btn-mpesa');
            } else {
                checkoutSubmit.textContent = 'Place order';
                checkoutSubmit.classList.remove('btn-mpesa');
            }
        };


        bindCartItemControls(checkoutItems);
        renderCheckout();
        updatePaymentButton();

        paymentInputs.forEach((input) => {
            input.addEventListener('change', updatePaymentButton);
        });

        checkoutForm?.addEventListener('submit', () => {
            renderCheckout();
        });

        if (checkoutPage.dataset.orderSuccess) {
            writeCart([]);
            updateCartCount();
            renderCheckout();
        }
    }

    updateCartCount();

    const syncWishlistButtons = () => {
        const items = readWishlist();
        const ids = new Set(items.map((item) => item.id));
        document.querySelectorAll('[data-wishlist-toggle]').forEach((button) => {
            const productId = button.dataset.productId;
            if (!productId) {
                return;
            }
            button.classList.toggle('is-active', ids.has(productId));
        });
    };

    document.querySelectorAll('[data-wishlist-toggle]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const productId = button.dataset.productId;
            if (!productId) {
                button.classList.toggle('is-active');
                return;
            }

            const items = readWishlist();
            const existingIndex = items.findIndex((item) => item.id === productId);
            if (existingIndex >= 0) {
                items.splice(existingIndex, 1);
                writeWishlist(items);
                button.classList.remove('is-active');
            } else {
                items.push({
                    id: productId,
                    name: button.dataset.productName || 'Product',
                    price: Number.parseFloat(button.dataset.productPrice || '0'),
                    image: button.dataset.productImage || '',
                    url: button.dataset.productUrl || '',
                });
                writeWishlist(items);
                button.classList.add('is-active');
            }

            updateWishlistCount();
        });
    });

    // Hero slider removed per request; keeping carousel only.
    updateWishlistCount();
    syncWishlistButtons();

    document.querySelectorAll('[data-maasai-slider]').forEach((slider) => {
        const slides = Array.from(slider.querySelectorAll('[data-maasai-slide]'));
        const controls = Array.from(slider.querySelectorAll('[data-maasai-slide-control]'));
        if (slides.length <= 1) {
            return;
        }

        let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
        activeIndex = activeIndex >= 0 ? activeIndex : 0;
        let interval = null;

        const showSlide = (nextIndex) => {
            activeIndex = (nextIndex + slides.length) % slides.length;
            slides.forEach((slide, index) => {
                slide.classList.toggle('is-active', index === activeIndex);
            });
            controls.forEach((control, index) => {
                control.classList.toggle('is-active', index === activeIndex);
                control.setAttribute('aria-current', index === activeIndex ? 'true' : 'false');
            });
        };

        const stopAutoplay = () => {
            if (interval) {
                window.clearInterval(interval);
                interval = null;
            }
        };

        const startAutoplay = () => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || interval) {
                return;
            }
            interval = window.setInterval(() => {
                showSlide(activeIndex + 1);
            }, 5200);
        };

        controls.forEach((control) => {
            control.addEventListener('click', () => {
                const index = Number.parseInt(control.dataset.slideIndex || '0', 10);
                showSlide(Number.isNaN(index) ? 0 : index);
                stopAutoplay();
                startAutoplay();
            });
        });

        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('mouseleave', startAutoplay);
        slider.addEventListener('focusin', stopAutoplay);
        slider.addEventListener('focusout', startAutoplay);
        showSlide(activeIndex);
        startAutoplay();
    });

    const whatsappFloat = document.querySelector('[data-whatsapp-float]');
    if (whatsappFloat) {
        const link = whatsappFloat.querySelector('[data-whatsapp-link]');
        const tooltip = whatsappFloat.querySelector('[data-whatsapp-tooltip]');
        const number = (whatsappFloat.dataset.whatsappNumber || '').replace(/[^\d]/g, '');
        const message = (whatsappFloat.dataset.whatsappMessage || '').trim();
        const enableScroll = whatsappFloat.dataset.whatsappScroll !== 'false';
        const threshold = Number.parseInt(whatsappFloat.dataset.whatsappThreshold || '180', 10);
        const baseUrl = `https://wa.me/${number || ''}`;
        const url = message ? `${baseUrl}?text=${encodeURIComponent(message)}` : baseUrl;

        if (link) {
            link.setAttribute('href', url);
        }

        const setVisibility = () => {
            if (!enableScroll) {
                whatsappFloat.classList.add('is-visible');
                return;
            }
            const shouldShow = window.scrollY >= (Number.isNaN(threshold) ? 180 : threshold);
            whatsappFloat.classList.toggle('is-visible', shouldShow);
        };

        setVisibility();
        if (enableScroll) {
            let ticking = false;
            window.addEventListener('scroll', () => {
                if (ticking) {
                    return;
                }
                ticking = true;
                window.requestAnimationFrame(() => {
                    setVisibility();
                    ticking = false;
                });
            }, { passive: true });
        }

        let tooltipTimer = null;
        if (tooltip) {
            whatsappFloat.addEventListener('mouseenter', () => {
                whatsappFloat.classList.add('is-tooltip-visible');
                if (tooltipTimer) {
                    clearTimeout(tooltipTimer);
                }
                tooltipTimer = window.setTimeout(() => {
                    whatsappFloat.classList.remove('is-tooltip-visible');
                }, 3000);
            });
            whatsappFloat.addEventListener('mouseleave', () => {
                whatsappFloat.classList.remove('is-tooltip-visible');
                if (tooltipTimer) {
                    clearTimeout(tooltipTimer);
                    tooltipTimer = null;
                }
            });
        }
    }
});
