<?php

namespace Modules\Order\Domain;

interface OrderRepositoryInterface
{
    public function save(OrderEntity $order): OrderEntity;

    public function findById(int $id): ?OrderEntity;

    public function findByStatus(string $status): array;

    public function delete(int $id): bool;
}
