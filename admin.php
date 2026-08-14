<?php
ob_start();
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Gold Scents</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #0f0f11;
            color: #ececec;
            margin: 0;
            padding: 30px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #17171a;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            border: 1px solid #26262b;
        }
        h2 {
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #d4af37;
            margin-top: 0;
            border-bottom: 2px solid #26262b;
            padding-bottom: 15px;
        }
        h3 {
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #fff;
            border-bottom: 1px solid #26262b;
            padding-bottom: 10px;
            margin-top: 35px;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            color: #d4af37;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            background-color: #0f0f11;
            border: 1px solid #333;
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
            outline: none;
        }
        input:focus { border-color: #d4af37; }
        input[type="file"] {
            background: #0f0f11;
            padding: 10px;
            cursor: pointer;
            color: #aaa;
        }
        .btn {
            background-color: #d4af37;
            color: #0f0f11;
            border: none;
            padding: 12px 24px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover { background-color: #f3cf65; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #0f0f11;
            border-radius: 6px;
            overflow: hidden;
        }
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #222;
            font-size: 0.95rem;
        }
        th {
            background-color: #070708;
            color: #d4af37;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        .delete-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 3px;
            font-weight: bold;
        }
        .delete-btn:hover { background-color: #cc0000; }
        
        .complete-order-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            margin-top: 15px;
            transition: background-color 0.2s;
        }
        .complete-order-btn:hover { background-color: #218838; }

        .order-card-box {
            border: 1px solid #26262b;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            background: #0f0f11;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            border-bottom: 1px solid #222;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .order-items-list {
            font-size: 0.9rem;
            color: #ccc;
            padding-left: 20px;
        }
        .nav-links {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #26262b;
        }
        .nav-links a { text-decoration: none; font-weight: bold; transition: color 0.3s; }
        .nav-links a:hover { color: #d4af37 !important; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Gold Scents - Management Console</h2>
        
        <h3>Customer Sales Orders</h3>
        <div id="orders-log-container">Loading active customer orders...</div>
        
        <form id="add-product-form" enctype="multipart/form-data">
            <h3>Add New Product</h3>
            <div class="form-group">
                <label for="products_name">Product Name</label>
                <input type="text" id="products_name" name="products_name" placeholder="e.g. Royal Oud Eau De Parfum" required>
            </div>
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" step="0.01" id="price" name="price" placeholder="150.00" required>
            </div>
            <div class="form-group">
                <label for="image_file">Product Display Image</label>
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
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="inventory-list"></tbody>
        </table>

        <div class="nav-links">
            <a href="index.html" style="color: #fff;">← Visit Storefront</a>
            <a href="logout.php" style="color: #ff4d4d;">Logout & Sign Out ➔</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            loadInventory();
            loadOrders();
        });

        async function loadOrders() {
            try {
                const response = await fetch('api/get_orders.php');
                const orders = await response.json();
                const container = document.getElementById('orders-log-container');
                container.innerHTML = '';

                if (orders.length === 0) {
                    container.innerHTML = '<p style="color: #888; font-style: italic; padding: 10px 0;">No pending customer orders.</p>';
                    return;
                }

                orders.forEach(order => {
                    const box = document.createElement('div');
                    box.className = 'order-card-box';
                    
                    let itemsHtml = '';
                    order.items.forEach(item => {
                        itemsHtml += `<li>${item.product_name} (x${item.quantity}) - $${parseFloat(item.price).toFixed(2)} each</li>`;
                    });

                    box.innerHTML = `
                        <div class="order-header">
                            <span style="color: #d4af37;">Order #${order.id}</span>
                            <span style="font-size: 0.85rem; color:#888;">${order.created_at}</span>
                        </div>
                        <p style="margin: 5px 0;"><strong>Customer:</strong> ${order.customer_name} (${order.customer_email})</p>
                        <p style="margin: 5px 0;"><strong>Total Received:</strong> <span style="color: #28a745; font-weight: bold;">$${parseFloat(order.total_price).toFixed(2)}</span></p>
                        <p style="margin: 12px 0 5px 0; font-weight: bold; color: #d4af37;">Items Purchased:</p>
                        <ul class="order-items-list">${itemsHtml}</ul>
                        <button class="complete-order-btn" onclick="deleteOrder(${order.id})">✓ Mark Delivered & Remove</button>
                    `;
                    container.appendChild(box);
                });
            } catch (error) {
                console.error("Error loading orders log:", error);
            }
        }

        async function deleteOrder(id) {
            if (!confirm("Confirm that Order #" + id + " has been delivered and can be removed?")) return;

            try {
                const response = await fetch('api/delete_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const rawText = await response.text();
                let result;

                try {
                    result = JSON.parse(rawText);
                } catch (e) {
                    alert("PHP Server Error Output:\n" + rawText);
                    return;
                }

                if (result.success) {
                    alert("✓ Order #" + id + " marked as delivered and removed!");
                    loadOrders(); // Instantly refresh active orders list
                } else {
                    alert("Unable to remove order:\n" + result.error);
                }
            } catch (error) {
                console.error("Error deleting order:", error);
                alert("Network Error: " + error.message);
            }
        }

        async function loadInventory() {
            try {
                const response = await fetch('api/products.php');
                const products = await response.json();
                const listContainer = document.getElementById('inventory-list');
                listContainer.innerHTML = '';

                if (products.length === 0) {
                    listContainer.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #888; padding: 20px;">No inventory items found.</td></tr>`;
                    return;
                }

                products.forEach(product => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${product.id}</td>
                        <td><img src="${product.image_url}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #333;"></td>
                        <td><strong style="color: #fff;">${product.products_name}</strong></td>
                        <td style="color: #d4af37;">$${parseFloat(product.price).toFixed(2)}</td>
                        <td><button class="delete-btn" onclick="deleteProduct(${product.id})">Delete</button></td>
                    `;
                    listContainer.appendChild(tr);
                });
            } catch (error) {
                console.error("Error loading products inventory:", error);
            }
        }

        document.getElementById('add-product-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('api/add_product.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    alert('✨ Product added to inventory successfully!');
                    this.reset();
                    loadInventory();
                } else { 
                    alert('Upload Error: ' + result.error); 
                }
            } catch (error) { 
                console.error(error); 
                alert('An unexpected network error occurred.');
            }
        });

        async function deleteProduct(id) {
            if (!confirm("Permanently delete this product from the store catalog?")) return;
            try {
                const response = await fetch('api/delete_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                if (result.success) { 
                    loadInventory(); 
                } else { 
                    alert(result.error); 
                }
            } catch (error) { 
                console.error(error); 
            }
        }
    </script>
</body>
</html>