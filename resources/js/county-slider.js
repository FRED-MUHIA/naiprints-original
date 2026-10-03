export function mountCountySliders() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    document.querySelectorAll('.county-slider-viewport').forEach((viewport) => {
        const track = viewport.querySelector('.county-slider-track');
        const pills = [...viewport.querySelectorAll('.county-slider-pill')];
        if (!track || !pills.length) return;

        let visible = false;
        let frame = null;

        const paint = () => {
            frame = null;
            if (!visible || document.hidden || reducedMotion.matches) return;

            const bounds = viewport.getBoundingClientRect();
            const fadeWidth = Math.min(64, bounds.width * 0.08);
            // Read positions together before updating styles. A tab stays invisible
            // while clipped, then fades as one complete pill inside the viewport.
            const opacities = pills.map((pill) => {
                const rect = pill.getBoundingClientRect();
                const clearance = Math.min(rect.left - bounds.left, bounds.right - rect.right);
                return Math.max(0, Math.min(1, clearance / fadeWidth));
            });
            pills.forEach((pill, index) => {
                pill.style.opacity = String(opacities[index]);
            });
            frame = window.requestAnimationFrame(paint);
        };

        const sync = () => {
            if (frame !== null) window.cancelAnimationFrame(frame);
            frame = null;
            if (reducedMotion.matches) {
                pills.forEach((pill) => pill.style.removeProperty('opacity'));
            } else if (visible && !document.hidden) {
                paint();
            }
        };

        new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting;
            sync();
        }).observe(viewport);
        reducedMotion.addEventListener('change', sync);
        document.addEventListener('visibilitychange', sync);
    });
}
