<?php

require_once __DIR__ . '/auth_config.php';

class CsrfProtection {
    public const TOKEN_SESSION_KEY = 'csrf_token';
    public const HEADER_NAME = 'X-CSRF-Token';

    public static function ensure_session(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function get_token(): string {
        self::ensure_session();

        if (
            empty($_SESSION[self::TOKEN_SESSION_KEY])
            || !is_string($_SESSION[self::TOKEN_SESSION_KEY])
        ) {
            $_SESSION[self::TOKEN_SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::TOKEN_SESSION_KEY];
    }

    public static function validate_request(): void {
        self::ensure_session();

        $header_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $post_token = $_POST['csrf_token'] ?? '';
        $provided_token = $header_token !== '' ? $header_token : $post_token;
        $session_token = $_SESSION[self::TOKEN_SESSION_KEY] ?? '';

        if (
            $session_token === ''
            || $provided_token === ''
            || !hash_equals($session_token, $provided_token)
        ) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid CSRF token. Refresh and try again.',
            ]);
            exit;
        }
    }
}
