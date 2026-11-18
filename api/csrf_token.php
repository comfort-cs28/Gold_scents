<?php
header('Content-Type: application/json');

require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../config/csrf.php';
require_admin_session();

echo json_encode([
    'success' => true,
    'csrf_token' => CsrfProtection::get_token(),
]);
