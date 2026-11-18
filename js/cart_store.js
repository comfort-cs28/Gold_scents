function load_cart() {
    try {
        const raw_cart = localStorage.getItem(CART_STORAGE_KEY);
        return raw_cart ? JSON.parse(raw_cart) : [];
    } catch (error) {
        console.error('Failed to parse cart:', error);
        return [];
    }
}

function save_cart(cart_items) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart_items));
}

function clear_cart() {
    localStorage.removeItem(CART_STORAGE_KEY);
}

function get_cart_count(cart_items) {
    return cart_items.reduce((total, item) => total + item.quantity, 0);
}

function get_cart_total(cart_items) {
    return cart_items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
}

function find_cart_item(cart_items, product_id) {
    return cart_items.find((item) => item.id === product_id);
}

function add_item_to_cart(cart_items, product, max_stock) {
    const existing_item = find_cart_item(cart_items, product.id);

    if (existing_item) {
        if (existing_item.quantity >= max_stock) {
            return { cart: cart_items, added: false, reason: 'stock_limit' };
        }
        existing_item.quantity += 1;
    } else {
        if (max_stock < MIN_STOCK_AVAILABLE) {
            return { cart: cart_items, added: false, reason: 'out_of_stock' };
        }
        cart_items.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            max_stock: max_stock,
        });
    }

    save_cart(cart_items);
    return { cart: cart_items, added: true, reason: 'ok' };
}

function update_item_quantity(cart_items, index, next_quantity) {
    if (index < 0 || index >= cart_items.length) {
        return cart_items;
    }

    const item = cart_items[index];
    const max_allowed = item.max_stock || 99;

    if (next_quantity < MIN_STOCK_AVAILABLE) {
        cart_items.splice(index, 1);
    } else {
        item.quantity = Math.min(next_quantity, max_allowed);
    }

    save_cart(cart_items);
    return cart_items;
}

function build_whatsapp_product_link(product_name) {
    const message = encodeURIComponent(
        `Hi Gold Scents! I want to order: ${product_name}`
    );
    return `https://wa.me/${WHATSAPP_NUMBER}?text=${message}`;
}
