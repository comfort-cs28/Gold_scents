<?php

require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../config/csrf.php';

function require_admin_session(bool $require_csrf = false): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $is_logged_in = isset($_SESSION[AdminAuth::SESSION_KEY])
        && $_SESSION[AdminAuth::SESSION_KEY] === true;

    if (!$is_logged_in) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized. Please log in again.',
        ]);
        exit;
    }

    if ($require_csrf) {
        CsrfProtection::validate_request();
    }
}
