const pad = (value) => String(value).padStart(2, '0');

export function mountClothingRails(scope = document) {
    const rails = [...scope.querySelectorAll('[data-clothing-rail]')];

    rails.forEach((rail) => {
        if (rail.dataset.railMounted === 'true') return;
        rail.dataset.railMounted = 'true';

        const viewport = rail.querySelector('[data-rail-viewport]');
        const track = rail.querySelector('[data-rail-track]');
        const items = [...rail.querySelectorAll('[data-rail-item]')];
        const name = rail.querySelector('[data-rail-name]');
        const category = rail.querySelector('[data-rail-category]');
        const price = rail.querySelector('[data-rail-price]');
        const link = rail.querySelector('[data-rail-link]');
        const currentCounter = rail.querySelector('[data-rail-current]');
        const playButton = rail.querySelector('[data-rail-play]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const interval = Math.max(3000, Math.min(12000, Number(rail.dataset.interval) || 6500));
        const autoRequested = rail.dataset.autoplay === 'true' && !reduceMotion.matches;
        let current = Math.max(0, items.findIndex((item) => item.classList.contains('is-active')));
        let timer = null;
        let motionTimer = null;
        let viewTimer = null;
        let anchorFrame = null;
        let pointerStart = null;
        let suppressNextTouchClick = false;
        let touchClickTimer = null;
        let temporaryPause = false;
        let userPaused = !autoRequested;
        let isVisible = true;

        if (!autoRequested && playButton) playButton.hidden = true;

        if (!viewport || !track || items.length < 2) {
            if (playButton) playButton.hidden = true;
            rail.querySelectorAll('[data-rail-prev], [data-rail-next]').forEach((button) => { button.hidden = true; });
            return;
        }

        const updateHangerPositions = () => {
            const viewportRect = viewport.getBoundingClientRect();
            const width = Math.max(1, viewportRect.width);
            items.forEach((item) => {
                const rect = item.getBoundingClientRect();
                const position = Math.min(1, Math.max(0, (rect.left + rect.width / 2 - viewportRect.left) / width));
                const sag = 57 * 2 * position * (1 - position);
                item.style.setProperty('--rail-drop', `${sag.toFixed(1)}px`);
            });
        };

        const followRailCurve = () => {
            if (anchorFrame !== null) window.cancelAnimationFrame(anchorFrame);
            const started = performance.now();
            const step = (now) => {
                updateHangerPositions();
                if (now - started < 820) anchorFrame = window.requestAnimationFrame(step);
                else anchorFrame = null;
            };
            anchorFrame = window.requestAnimationFrame(step);
        };

        const centerCurrent = () => {
            const selected = items[current];
            if (!selected) return;
            const left = selected.offsetLeft + (selected.offsetWidth / 2);
            const target = (viewport.clientWidth / 2) - left;
            track.style.transform = `translate3d(${target}px, 0, 0)`;
            followRailCurve();
        };

        const triggerMotion = () => {
            if (reduceMotion.matches) return;
            rail.classList.remove('is-moving');
            void rail.offsetWidth;
            rail.classList.add('is-moving');
            if (motionTimer !== null) window.clearTimeout(motionTimer);
            motionTimer = window.setTimeout(() => {
                rail.classList.remove('is-moving');
                motionTimer = null;
            }, 1150);
        };

        const render = (animate = true) => {
            const selected = items[current];
            if (!selected) return;

            items.forEach((item, index) => {
                const active = index === current;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-current', active ? 'true' : 'false');
                const button = item.querySelector('[data-rail-select]');
                if (button) button.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            if (viewTimer !== null) window.clearTimeout(viewTimer);
            items.forEach((item) => item.classList.remove('is-showing-back'));
            if (selected.dataset.hasBack === 'true') {
                viewTimer = window.setTimeout(() => {
                    selected.classList.add('is-showing-back');
                    viewTimer = null;
                }, 1250);
            }

            if (name) name.textContent = selected.dataset.name || '';
            if (category) category.textContent = selected.dataset.category || '';
            if (price) price.textContent = selected.dataset.price || '';
            if (link) link.href = selected.dataset.url || '/shop';
            if (currentCounter) currentCounter.textContent = pad(current + 1);
            centerCurrent();
            if (animate) triggerMotion();

            const copy = rail.querySelector('.clothing-rail-product-copy');
            if (copy) {
                copy.style.animation = 'none';
                void copy.offsetWidth;
                copy.style.animation = '';
            }
        };

        const stopTimer = () => {
            if (timer !== null) window.clearInterval(timer);
            timer = null;
        };

        const updatePlayButton = () => {
            if (!playButton) return;
            const paused = userPaused || temporaryPause || !isVisible || reduceMotion.matches;
            playButton.textContent = paused ? 'Play' : 'Pause';
            playButton.setAttribute('aria-pressed', paused ? 'true' : 'false');
            playButton.setAttribute('aria-label', paused ? 'Resume automatic product browsing' : 'Pause automatic product browsing');
        };

        const syncTimer = () => {
            stopTimer();
            updatePlayButton();
            if (!autoRequested || userPaused || temporaryPause || !isVisible || reduceMotion.matches) return;
            timer = window.setInterval(() => {
                current = (current + 1) % items.length;
                render();
            }, interval);
        };

        const select = (index, pauseForInteraction = true) => {
            current = (index + items.length) % items.length;
            if (pauseForInteraction) userPaused = true;
            render();
            syncTimer();
        };

        rail.querySelector('[data-rail-prev]')?.addEventListener('click', () => select(current - 1));
        rail.querySelector('[data-rail-next]')?.addEventListener('click', () => select(current + 1));
        playButton?.addEventListener('click', () => {
            userPaused = !userPaused;
            if (!userPaused) temporaryPause = false;
            syncTimer();
        });

        items.forEach((item, index) => {
            item.querySelector('[data-rail-select]')?.addEventListener('click', (event) => {
                if (suppressNextTouchClick) {
                    event.preventDefault();
                    suppressNextTouchClick = false;
                    if (touchClickTimer !== null) window.clearTimeout(touchClickTimer);
                    touchClickTimer = null;
                    return;
                }
                if (index === current && item.dataset.url) {
                    window.location.assign(item.dataset.url);
                    return;
                }
                select(index);
            });
            item.addEventListener('pointerenter', () => {
                if (window.matchMedia('(hover: hover)').matches) select(index, false);
            });
        });

        rail.addEventListener('mouseenter', () => {
            if (!window.matchMedia('(hover: hover)').matches) return;
            temporaryPause = true;
            syncTimer();
        });
        rail.addEventListener('mouseleave', () => {
            temporaryPause = false;
            syncTimer();
        });
        rail.addEventListener('focusin', () => {
            temporaryPause = true;
            syncTimer();
        });
        rail.addEventListener('focusout', (event) => {
            if (!rail.contains(event.relatedTarget)) {
                temporaryPause = false;
                syncTimer();
            }
        });

        viewport.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                select(current - 1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                select(current + 1);
            }
        });

        viewport.addEventListener('pointerdown', (event) => {
            if (event.pointerType !== 'mouse') pointerStart = event.clientX;
        }, { passive: true });
        viewport.addEventListener('pointerup', (event) => {
            if (pointerStart === null) return;
            const delta = event.clientX - pointerStart;
            pointerStart = null;
            if (Math.abs(delta) > 38) {
                suppressNextTouchClick = true;
                if (touchClickTimer !== null) window.clearTimeout(touchClickTimer);
                touchClickTimer = window.setTimeout(() => {
                    suppressNextTouchClick = false;
                    touchClickTimer = null;
                }, 500);
                select(current + (delta < 0 ? 1 : -1));
            }
        }, { passive: true });
        viewport.addEventListener('pointercancel', () => { pointerStart = null; }, { passive: true });
        viewport.addEventListener('wheel', (event) => {
            if (Math.abs(event.deltaX) < 28 || Math.abs(event.deltaX) <= Math.abs(event.deltaY)) return;
            event.preventDefault();
            select(current + (event.deltaX > 0 ? 1 : -1));
        }, { passive: false });

        window.addEventListener('resize', () => {
            centerCurrent();
            updateHangerPositions();
        }, { passive: true });
        document.addEventListener('visibilitychange', () => {
            isVisible = document.visibilityState === 'visible';
            syncTimer();
        });
        reduceMotion.addEventListener?.('change', syncTimer);

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(([entry]) => {
                isVisible = entry.isIntersecting;
                syncTimer();
            }, { threshold: 0.12 });
            observer.observe(rail);
        }

        render(false);
        syncTimer();
    });
}
