export function initLanding() {
    const nav = document.querySelector('[data-landing-nav]');

    if (nav) {
        const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 16);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const revealTargets = document.querySelectorAll('[data-reveal]');

    if (prefersReduced || !('IntersectionObserver' in window)) {
        revealTargets.forEach((el) => el.classList.add('is-visible'));
    } else {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

        revealTargets.forEach((el) => io.observe(el));
    }

    const counters = document.querySelectorAll('[data-count]');

    const runCounter = (el) => {
        const target = parseInt(el.dataset.count, 10) || 0;

        if (prefersReduced) {
            el.textContent = target.toLocaleString('id-ID');
            return;
        }

        const duration = 1400;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased).toLocaleString('id-ID');

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    };

    if ('IntersectionObserver' in window) {
        const co = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    runCounter(entry.target);
                    co.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach((el) => co.observe(el));
    } else {
        counters.forEach(runCounter);
    }
}
