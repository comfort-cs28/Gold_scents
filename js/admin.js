const SINGULAR_COUNT = 1;

let csrf_token = '';

document.addEventListener('DOMContentLoaded', async () => {
    await refresh_csrf_token();
    load_inventory();
    load_orders();
});

async function refresh_csrf_token() {
    const response = await fetch('api/csrf_token.php');
    const result = await response.json();
    if (!result.success) {
        show_toast('Session expired. Please log in again.', 'error');
        return;
    }
    csrf_token = result.csrf_token;
}

function csrf_headers(extra_headers = {}) {
    return {
        ...extra_headers,
        'X-CSRF-Token': csrf_token,
    };
}

function build_status_options(current_status) {
    return Object.values(ORDER_STATUS).map((status_value) => {
        const selected = status_value === current_status ? 'selected' : '';
        return `<option value="${status_value}" ${selected}>${ORDER_STATUS_LABELS[status_value]}</option>`;
    }).join('');
}

function set_orders_count(count) {
    const count_el = document.getElementById('orders-count');
    if (count_el) {
        count_el.textContent = `${count} order${count === SINGULAR_COUNT ? '' : 's'}`;
    }
}

function set_inventory_count(count) {
    const count_el = document.getElementById('inventory-count');
    if (count_el) {
        count_el.textContent = `${count} item${count === SINGULAR_COUNT ? '' : 's'}`;
    }
}

async function load_orders() {
    try {
        const response = await fetch('api/get_orders.php');
        const orders = await response.json();
        const container = document.getElementById('orders-log-container');
        container.innerHTML = '';

        if (!Array.isArray(orders) || orders.length === EMPTY_CART_LENGTH) {
            set_orders_count(EMPTY_CART_LENGTH);
            container.innerHTML = '<p class="empty-state">No customer orders yet.</p>';
            return;
        }

        set_orders_count(orders.length);

        orders.forEach((order) => {
            const box = document.createElement('div');
            box.className = 'order-card-box';

            const items_html = order.items.map((item) => (
                `<li>${escape_html(item.product_name)} (x${item.quantity}) — ${format_money(item.price)}</li>`
            )).join('');

            const proof_html = order.payment_proof_url
                ? `<p><strong>Payment Proof:</strong> <a href="${escape_html(order.payment_proof_url)}" target="_blank" rel="noopener">View screenshot</a></p>`
                : '<p><strong>Payment Proof:</strong> —</p>';

            box.innerHTML = `
                <div class="order-header">
                    <span class="order-id">Order #${order.id}</span>
                    <span class="status-pill" data-status="${escape_html(order.status)}">${ORDER_STATUS_LABELS[order.status] || order.status}</span>
                    <span class="order-meta">${escape_html(order.created_at)}</span>
                </div>
                <p><strong>Customer:</strong> ${escape_html(order.customer_name)} (${escape_html(order.customer_email)})</p>
                <p><strong>Phone:</strong> ${escape_html(order.customer_phone || '—')}</p>
                <p><strong>Address:</strong> ${escape_html(order.delivery_address || '—')}</p>
                <p><strong>MoMo ID:</strong> ${escape_html(order.momo_transaction_id || '—')}</p>
                ${proof_html}
                <p><strong>Total:</strong> <span class="order-total">${format_money(order.total_price)}</span></p>
                <p class="order-id" style="margin-bottom:4px;">Items</p>
                <ul>${items_html}</ul>
                <div class="status-row">
                    <select class="status-select" data-order-status="${order.id}">
                        ${build_status_options(order.status)}
                    </select>
                    <button class="btn" type="button" data-update-order="${order.id}">Update Status</button>
                    <button class="delete-btn" type="button" data-delete-order="${order.id}">Remove</button>
                </div>
            `;
            container.appendChild(box);
        });
    } catch (error) {
        console.error(error);
        show_toast('Could not load orders.', 'error');
    }
}

async function update_order_status(order_id) {
    const status_select = document.querySelector(`[data-order-status="${order_id}"]`);
    const status_value = status_select.value;
    try {
        const response = await fetch('api/update_order_status.php', {
            method: 'POST',
            headers: csrf_headers({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ id: order_id, status: status_value }),
        });
        const result = await response.json();
        if (result.success) {
            show_toast(`Order #${order_id} → ${ORDER_STATUS_LABELS[status_value]}`);
            load_orders();
        } else {
            show_toast(result.error || 'Update failed', 'error');
        }
    } catch (error) {
        show_toast('Network error updating status.', 'error');
    }
}

async function delete_order(order_id) {
    if (!confirm(`Remove order #${order_id}?`)) return;
    try {
        const response = await fetch('api/delete_order.php', {
            method: 'POST',
            headers: csrf_headers({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ id: order_id }),
        });
        const result = await response.json();
        if (result.success) {
            show_toast(`Order #${order_id} removed`);
            load_orders();
        } else {
            show_toast(result.error || 'Delete failed', 'error');
        }
    } catch (error) {
        show_toast('Network error deleting order.', 'error');
    }
}

async function load_inventory() {
    try {
        const response = await fetch('api/products.php');
        const products = await response.json();
        const list = document.getElementById('inventory-list');
        list.innerHTML = '';

        if (!Array.isArray(products) || products.length === EMPTY_CART_LENGTH) {
            set_inventory_count(EMPTY_CART_LENGTH);
            list.innerHTML = '<tr><td colspan="7" class="empty-state">No inventory items.</td></tr>';
            return;
        }

        set_inventory_count(products.length);

        products.forEach((product) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td><img class="inventory-thumb" src="${escape_html(product.image_url)}" alt=""></td>
                <td><strong>${escape_html(product.products_name)}</strong></td>
                <td>${escape_html(product.notes || '—')}</td>
                <td class="price-cell">${format_money(product.price)}</td>
                <td>${product.stock_quantity}</td>
                <td><button class="delete-btn" type="button" data-delete-product="${product.id}">Delete</button></td>
            `;
            list.appendChild(row);
        });
    } catch (error) {
        show_toast('Could not load inventory.', 'error');
    }
}

async function delete_product(product_id) {
    if (!confirm('Permanently delete this product?')) return;
    try {
        const response = await fetch('api/delete_product.php', {
            method: 'POST',
            headers: csrf_headers({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ id: product_id }),
        });
        const result = await response.json();
        if (result.success) {
            show_toast('Product deleted');
            load_inventory();
        } else {
            show_toast(result.error || 'Delete failed', 'error');
        }
    } catch (error) {
        show_toast('Network error deleting product.', 'error');
    }
}

document.getElementById('orders-log-container')?.addEventListener('click', (event) => {
    const update_btn = event.target.closest('[data-update-order]');
    if (update_btn) {
        update_order_status(Number(update_btn.dataset.updateOrder));
        return;
    }

    const delete_btn = event.target.closest('[data-delete-order]');
    if (delete_btn) {
        delete_order(Number(delete_btn.dataset.deleteOrder));
    }
});

document.getElementById('inventory-list')?.addEventListener('click', (event) => {
    const delete_btn = event.target.closest('[data-delete-product]');
    if (delete_btn) {
        delete_product(Number(delete_btn.dataset.deleteProduct));
    }
});

document.getElementById('add-product-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form_data = new FormData(event.target);
    form_data.append('csrf_token', csrf_token);

    try {
        const response = await fetch('api/add_product.php', {
            method: 'POST',
            headers: csrf_headers(),
            body: form_data,
        });
        const result = await response.json();

        if (result.success) {
            show_toast('Product added to inventory');
            event.target.reset();
            document.getElementById('stock_quantity').value = '10';
            load_inventory();
        } else {
            show_toast(result.error || 'Upload failed', 'error');
        }
    } catch (error) {
        show_toast('Network error adding product.', 'error');
    }
});
