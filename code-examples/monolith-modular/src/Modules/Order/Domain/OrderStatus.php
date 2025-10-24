<?php

namespace Modules\Order\Domain;

class OrderStatus
{
    public const PENDING = 'pending';
    public const PREPARING = 'preparing';
    public const READY = 'ready';
    public const DELIVERING = 'delivering';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public static function isValid(string $status): bool
    {
        return in_array($status, [
            self::PENDING,
            self::PREPARING,
            self::READY,
            self::DELIVERING,
            self::DELIVERED,
            self::CANCELLED,
        ]);
    }
}
