<?php

class OrderStatus {
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public static function all(): array {
        return [
            self::PENDING,
            self::PAID,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }

    public static function is_valid(string $status): bool {
        return in_array($status, self::all(), true);
    }
}

class AppConfig {
    public const CURRENCY_PREFIX = 'GH₵';
    public const WHATSAPP_NUMBER = '233534048714';
    public const MOMO_NUMBER = '0598488853';
    public const MOMO_ACCOUNT_NAME = 'Yikpa Comfort Akorfa';
    public const DEFAULT_STOCK = 10;
}
