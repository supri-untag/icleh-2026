import './motion';
import $ from 'jquery';
import select2 from 'select2';

select2(window, $);
$('.js-select2').select2({ width: '100%' });

document.querySelectorAll('[data-speaker-tabs]').forEach((container) => {
    const tabs = Array.from(container.querySelectorAll('[role="tab"]'));

    const activateTab = (selectedTab) => {
        tabs.forEach((tab) => {
            const isSelected = tab === selectedTab;
            tab.setAttribute('aria-selected', String(isSelected));
            tab.tabIndex = isSelected ? 0 : -1;
            document.getElementById(tab.getAttribute('aria-controls')).hidden = !isSelected;
        });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab));
        tab.addEventListener('keydown', (event) => {
            let nextIndex;

            switch (event.key) {
                case 'ArrowRight':
                    nextIndex = (index + 1) % tabs.length;
                    break;
                case 'ArrowLeft':
                    nextIndex = (index - 1 + tabs.length) % tabs.length;
                    break;
                case 'Home':
                    nextIndex = 0;
                    break;
                case 'End':
                    nextIndex = tabs.length - 1;
                    break;
                default:
                    return;
            }

            event.preventDefault();
            activateTab(tabs[nextIndex]);
            tabs[nextIndex].focus();
        });
    });
});

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
