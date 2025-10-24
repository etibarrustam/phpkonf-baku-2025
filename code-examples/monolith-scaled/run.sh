#!/bin/bash

echo "monolith scaled check"
echo "health ping"
curl -s http://localhost:8001/ | jq .

echo ""
echo "create order"
ORDER_RESPONSE=$(curl -s -X POST http://localhost:8001/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "product_id": 1,
    "quantity": 2,
    "delivery_address": "Baku, Nizami street 123"
  }')

echo $ORDER_RESPONSE | jq .
ORDER_ID=$(echo $ORDER_RESPONSE | jq -r '.data.id')

echo ""
echo "round robin check"
for i in {1..5}; do
  echo "hit $i"
  curl -s http://localhost:8001/api/orders/$ORDER_ID | jq '{id: .data.id, instance: .served_by}'
done

echo ""
echo "done"
