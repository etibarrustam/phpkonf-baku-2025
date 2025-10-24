<?php

namespace Modules\Kitchen\Application;

interface KitchenServiceInterface
{
    public function addToQueue(int $orderId): void;

    public function markAsReady(int $orderId): void;

    public function getQueue(): array;
}
