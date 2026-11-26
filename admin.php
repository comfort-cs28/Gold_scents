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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/store.css">
    <link rel="stylesheet" href="css/store-pages.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-page">
    <header class="admin-topbar">
        <div class="admin-brand">
            <img class="logo-image" src="image/gold_scents_logo.png" alt="">
            <div>
                <div class="logo-text">gold<span>scents</span></div>
                <small>Staff Console</small>
            </div>
        </div>
        <div class="admin-top-actions">
            <a class="btn-ghost-link" href="index.html">Storefront</a>
            <a class="btn-ghost-link btn-logout" href="logout.php">Logout</a>
        </div>
    </header>

    <main class="admin-main">
        <section class="admin-intro">
            <p class="login-kicker">Management</p>
            <h1>Dashboard</h1>
            <p>Review MoMo orders, update fulfilment status, and keep the fragrance inventory current.</p>
        </section>

        <section class="admin-section" id="orders">
            <div class="admin-section-head">
                <h2>Customer Orders</h2>
                <span id="orders-count">Loading…</span>
            </div>
            <div id="orders-log-container">Loading orders...</div>
        </section>

        <section class="admin-section" id="add-product">
            <div class="admin-section-head">
                <h2>Add New Product</h2>
                <span>Upload to storefront</span>
            </div>
            <form id="add-product-form" enctype="multipart/form-data">
                <div class="admin-form-grid">
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
                    <div class="form-group form-span-2">
                        <label for="image_file">Product Image</label>
                        <input type="file" id="image_file" name="image_file" accept="image/*" required>
                    </div>
                    <div class="form-group form-span-2 admin-form-actions">
                        <button type="submit" class="btn">Add Product to Store</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="admin-section" id="inventory">
            <div class="admin-section-head">
                <h2>Current Inventory</h2>
                <span id="inventory-count">Loading…</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
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
            </div>
        </section>
    </main>

    <script src="js/constants.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>
