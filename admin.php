<?php
ob_start();
session_start();
require_once __DIR__ . '/config/auth_config.php';

if (!isset($_SESSION[AdminAuth::SESSION_KEY]) || $_SESSION[AdminAuth::SESSION_KEY] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Gold Scents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,500&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/store.css">
    <style>
        body { padding: 30px 20px; }
        .admin-wrap {
            width: min(1000px, 96%);
            margin: 0 auto;
            background: var(--bg-panel);
            padding: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        .admin-wrap h2 {
            margin-top: 0;
            color: var(--gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
        }
        .admin-wrap h3 {
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            margin-top: 34px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background: var(--bg-main);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #222;
            font-size: 0.9rem;
        }
        th {
            background: var(--bg-deep);
            color: var(--gold);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-family: 'Montserrat', sans-serif;
        }
        .delete-btn {
            background: var(--danger);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            font-weight: 700;
        }
        .order-card-box {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 16px;
            background: var(--bg-main);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            border-bottom: 1px solid #222;
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 14px;
        }
        .status-select { width: auto; min-width: 140px; }
        .nav-links {
            display: flex;
            justify-content: space-between;
            margin-top: 36px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }
        .nav-links a {
            text-decoration: none;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(212, 175, 55, 0.15);
            color: var(--gold);
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>
<body>
    <div class="admin-wrap">
        <h2>Gold Scents — Management Console</h2>

        <h3>Customer Sales Orders</h3>
        <div id="orders-log-container">Loading orders...</div>

        <form id="add-product-form" enctype="multipart/form-data">
            <h3>Add New Product</h3>
            <div class="form-group">
                <label for="products_name">Product Name</label>
                <input type="text" id="products_name" name="products_name" required>
            </div>
            <div class="form-group">
                <label for="notes">Scent Notes</label>
                <input type="text" id="notes" name="notes" placeholder="e.g. vanilla, musk, oud" required>
            </div>
            <div class="form-group">
                <label for="price">Price (GHS)</label>
                <input type="number" step="0.01" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="10" required>
            </div>
            <div class="form-group">
                <label for="image_file">Product Image</label>
                <input type="file" id="image_file" name="image_file" accept="image/*" required>
            </div>
            <button type="submit" class="btn">Add Product to Store</button>
        </form>

        <h3>Current Inventory</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Notes</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="inventory-list"></tbody>
        </table>

        <div class="nav-links">
            <a href="index.html" style="color:#fff;">← Visit Storefront</a>
            <a href="logout.php" style="color:var(--danger);">Logout</a>
        </div>
    </div>

    <script src="js/constants.js"></script>
    <script src="js/toast.js"></script>
    <script>
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

        async function load_orders() {
            try {
                const response = await fetch('api/get_orders.php');
                const orders = await response.json();
                const container = document.getElementById('orders-log-container');
                container.innerHTML = '';

                if (!Array.isArray(orders) || orders.length === EMPTY_CART_LENGTH) {
                    container.innerHTML = '<p class="empty-state">No customer orders yet.</p>';
                    return;
                }

                orders.forEach((order) => {
                    const box = document.createElement('div');
                    box.className = 'order-card-box';

                    const items_html = order.items.map((item) => (
                        `<li>${escape_html(item.product_name)} (x${item.quantity}) — ${format_money(item.price)}</li>`
                    )).join('');

                    const proof_html = order.payment_proof_url
                        ? `<p><strong>Payment Proof:</strong> <a href="${escape_html(order.payment_proof_url)}" target="_blank" rel="noopener" style="color:var(--gold);">View screenshot</a></p>`
                        : '<p><strong>Payment Proof:</strong> —</p>';

                    box.innerHTML = `
                        <div class="order-header">
                            <span style="color:var(--gold);">Order #${order.id}</span>
                            <span class="status-pill">${ORDER_STATUS_LABELS[order.status] || order.status}</span>
                            <span style="font-size:0.85rem;color:#888;">${escape_html(order.created_at)}</span>
                        </div>
                        <p><strong>Customer:</strong> ${escape_html(order.customer_name)} (${escape_html(order.customer_email)})</p>
                        <p><strong>Phone:</strong> ${escape_html(order.customer_phone || '—')}</p>
                        <p><strong>Address:</strong> ${escape_html(order.delivery_address || '—')}</p>
                        <p><strong>MoMo ID:</strong> ${escape_html(order.momo_transaction_id || '—')}</p>
                        ${proof_html}
                        <p><strong>Total:</strong> <span style="color:var(--success);font-weight:700;">${format_money(order.total_price)}</span></p>
                        <p style="color:var(--gold);font-weight:700;margin-bottom:4px;">Items</p>
                        <ul>${items_html}</ul>
                        <div class="status-row">
                            <select class="status-select" id="status-${order.id}">
                                ${build_status_options(order.status)}
                            </select>
                            <button class="btn" type="button" onclick="update_order_status(${order.id})">Update Status</button>
                            <button class="delete-btn" type="button" onclick="delete_order(${order.id})">Remove</button>
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
            const status_value = document.getElementById(`status-${order_id}`).value;
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
                    list.innerHTML = '<tr><td colspan="7" class="empty-state">No inventory items.</td></tr>';
                    return;
                }

                products.forEach((product) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td><img src="${escape_html(product.image_url)}" alt="" style="width:45px;height:45px;object-fit:cover;border-radius:4px;border:1px solid #333;"></td>
                        <td><strong>${escape_html(product.products_name)}</strong></td>
                        <td>${escape_html(product.notes || '—')}</td>
                        <td style="color:var(--gold);">${format_money(product.price)}</td>
                        <td>${product.stock_quantity}</td>
                        <td><button class="delete-btn" type="button" onclick="delete_product(${product.id})">Delete</button></td>
                    `;
                    list.appendChild(row);
                });
            } catch (error) {
                show_toast('Could not load inventory.', 'error');
            }
        }

        document.getElementById('add-product-form').addEventListener('submit', async (event) => {
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
    </script>
</body>
</html>
