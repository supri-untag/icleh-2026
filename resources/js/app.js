const publicHeader = document.querySelector('[data-public-header]');

if (publicHeader) {
    const syncHeaderState = () => {
        publicHeader.classList.toggle('is-scrolled', window.scrollY > 8);
    };

    syncHeaderState();
    window.addEventListener('scroll', syncHeaderState, { passive: true });
}

const mobileMenu = document.querySelector('.landing-mobile-menu');

mobileMenu?.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
        mobileMenu.open = false;
    }
});

const speakerCards = document.querySelectorAll('.speaker-card');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

if (speakerCards.length && 'IntersectionObserver' in window) {
    const speakerObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (!entry.isIntersecting) {
                return;
            }

            speakerObserver.unobserve(entry.target);

            if (!reducedMotion.matches) {
                entry.target.animate([
                    { opacity: 0, translate: '0 28px' },
                    { opacity: 1, translate: '0 0' },
                ], {
                    duration: 650,
                    delay: Math.min(index, 4) * 90,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    fill: 'backwards',
                });
            }
        });
    }, { threshold: 0.15 });

    speakerCards.forEach((card) => speakerObserver.observe(card));

    reducedMotion.addEventListener('change', () => {
        if (reducedMotion.matches) {
            speakerCards.forEach((card) => {
                card.getAnimations().forEach((animation) => animation.cancel());
            });
        }
    });
}
