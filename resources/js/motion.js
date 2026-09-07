import '../css/motion.css';

const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
const activeAnimations = new Map();
const candidates = [...document.querySelectorAll(
    'main h1, main h2, main article, main .card, main .journey-step, main .speaker-card, body:not(.public-page):not(.admin-shell) main > section',
)];
const candidateSet = new Set(candidates);
const targets = candidates.filter((element) => {
    for (let parent = element.parentElement; parent; parent = parent.parentElement) {
        if (candidateSet.has(parent)) {
            return false;
        }
    }

    return true;
});

if ('IntersectionObserver' in window && 'animate' in Element.prototype) {
    const observer = new IntersectionObserver((entries) => {
        let visibleIndex = 0;

        entries.forEach(({ target, isIntersecting }) => {
            if (!isIntersecting) {
                return;
            }

            observer.unobserve(target);

            if (motionPreference.matches || target.contains(document.activeElement)) {
                return;
            }

            const animation = target.animate([
                { opacity: 0, translate: '0 14px' },
                { opacity: 1, translate: '0 0' },
            ], {
                duration: 420,
                delay: Math.min(visibleIndex++, 3) * 55,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                fill: 'backwards',
            });

            activeAnimations.set(target, animation);
            const clearAnimation = () => activeAnimations.delete(target);
            animation.addEventListener('finish', clearAnimation, { once: true });
            animation.addEventListener('cancel', clearAnimation, { once: true });
        });
    }, { threshold: 0, rootMargin: '0px 0px -24px 0px' });

    targets.forEach((target) => observer.observe(target));

    document.addEventListener('focusin', (event) => {
        activeAnimations.forEach((animation, target) => {
            if (target.contains(event.target)) {
                animation.cancel();
            }
        });
    });

    motionPreference.addEventListener('change', () => {
        if (motionPreference.matches) {
            activeAnimations.forEach((animation) => animation.cancel());
        }
    });
}
