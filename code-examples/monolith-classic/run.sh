#!/bin/bash

echo "monolith classic run"
echo "build containers"

if docker compose version >/dev/null 2>&1; then
  compose_cmd() { docker compose "$@"; }
elif command -v docker-compose >/dev/null 2>&1; then
  compose_cmd() { docker-compose "$@"; }
else
  echo "docker compose command missing"
  exit 1
fi

compose_cmd up -d --build

echo "wait mysql little"
sleep 15

echo "migrate seed"
compose_cmd exec -T app php artisan migrate --force --seed

echo "hit api create"
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "product_id": 1,
    "quantity": 2,
    "delivery_address": "Test Street"
  }'

echo ""
echo "read order"
curl http://localhost:8000/api/orders/1

echo ""
echo "check kitchen queue"
curl http://localhost:8000/api/kitchen/queue

echo ""
echo "done"
