function render_inner_header(active_cart_count = 0) {
    return `
    <div class="announcement-bar">
        <button type="button" data-announcement-prev aria-label="Previous">‹</button>
        <span data-announcement-text>Free consultation with every fragrance order</span>
        <button type="button" data-announcement-next aria-label="Next">›</button>
    </div>
    <header class="site-header inner-header">
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
        <nav>
            <a href="index.html">Home</a>
            <a href="index.html#collection">Collection</a>
            <a href="track.html">Track</a>
            <a href="cart.html">Cart <span class="cart-icon" id="cart-count">${active_cart_count}</span></a>
            <a href="https://wa.me/233534048714" target="_blank" rel="noopener">WhatsApp</a>
        </nav>
    </header>
    `;
}
