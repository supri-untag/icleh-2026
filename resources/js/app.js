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
