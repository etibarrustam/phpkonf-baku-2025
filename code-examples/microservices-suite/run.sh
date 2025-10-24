#!/bin/bash

set -e

echo "microservices quick check"
echo ""

echo "order health"
ORDER_HEALTH=$(curl -s http://localhost:8005/health)
echo "  $ORDER_HEALTH"
echo ""

echo "payment health"
PAYMENT_HEALTH=$(curl -s http://localhost:8002/health)
echo "  $PAYMENT_HEALTH"
echo ""

echo "kitchen health"
KITCHEN_HEALTH=$(curl -s http://localhost:8003/health)
echo "  $KITCHEN_HEALTH"
echo ""

echo "delivery health"
DELIVERY_HEALTH=$(curl -s http://localhost:8004/health)
echo "  $DELIVERY_HEALTH"
echo ""

echo "create order flow"
ORDER_RESPONSE=$(curl -s -X POST http://localhost:8005/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "items": ["Pizza Margherita", "Coca Cola"],
    "total_amount": "25.50"
  }')
echo "$ORDER_RESPONSE"
echo ""

ORDER_ID=$(echo $ORDER_RESPONSE | grep -o '"id":[0-9]*' | grep -o '[0-9]*' | head -1)

if [ ! -z "$ORDER_ID" ]; then
  echo "order detail $ORDER_ID"
  curl -s http://localhost:8005/orders/$ORDER_ID | jq '.'
  echo ""

  echo "list orders"
  curl -s http://localhost:8005/orders | jq '.'
  echo ""

  echo "list payments"
  curl -s http://localhost:8002/payments | jq '.'
  echo ""

  echo "list kitchen"
  curl -s http://localhost:8003/kitchen | jq '.'
  echo ""

  echo "list deliveries"
  curl -s http://localhost:8004/deliveries | jq '.'
  echo ""
fi

echo "done"
