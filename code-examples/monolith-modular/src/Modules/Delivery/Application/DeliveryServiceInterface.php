<?php

namespace Modules\Delivery\Application;

interface DeliveryServiceInterface
{
    public function assignDelivery(int $orderId): void;

    public function markAsDelivered(int $orderId): void;

    public function getActiveDeliveries(): array;
}
