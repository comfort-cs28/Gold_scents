function create_logo_markup() {
    return `
        <a class="logo" href="index.html" aria-label="Gold Scents home">
            <svg class="logo-bottle" viewBox="0 0 40 52" aria-hidden="true">
                <path d="M14 8h12v6H14zM12 14h16l3 34H9L12 14z"></path>
                <path d="M16 4h8v4h-8z"></path>
            </svg>
            <div>
                <h1 class="logo-text">gold<span>scents</span></h1>
                <small>Luxury fragrance vault</small>
            </div>
        </a>
    `;
}

function init_announcement() {
    const bar = document.querySelector('.announcement-bar');
    if (!bar) return;

    const messages = [
        'Free consultation with every fragrance order',
        'Pay via MoMo · 0598488853 · Gold Scents',
        'Track your order anytime from the menu',
    ];
    let index = 0;
    const text_el = bar.querySelector('[data-announcement-text]');
    if (!text_el) return;

    bar.querySelector('[data-announcement-prev]')?.addEventListener('click', () => {
        index = (index - 1 + messages.length) % messages.length;
        text_el.textContent = messages[index];
    });
    bar.querySelector('[data-announcement-next]')?.addEventListener('click', () => {
        index = (index + 1) % messages.length;
        text_el.textContent = messages[index];
    });
}

function init_sticky_header() {
    const header = document.querySelector('.site-header[data-overlay-header]');
    const hero = document.querySelector('.hero');
    if (!header || !hero) return;

    const on_scroll = () => {
        const hero_height = hero.offsetHeight || 0;
        const should_solid = hero_height > 120 && window.scrollY > (hero_height * 0.55);
        header.classList.toggle('is-solid', should_solid);
    };

    on_scroll();
    window.addEventListener('scroll', on_scroll, { passive: true });
}

function init_filters() {
    document.querySelectorAll('.filter-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            button.parentElement.classList.toggle('is-open');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    init_announcement();
    init_sticky_header();
    init_filters();
});
