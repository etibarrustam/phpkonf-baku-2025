#!/bin/bash

set -e

echo "event saga run"
echo ""

echo "health grab"
for service in order-service payment-service kitchen-service delivery-service; do
    echo "$service"
    response=$(curl -s http://localhost:800${service: -1}/health 2>/dev/null || echo "down")
    echo "  $response"
done

echo ""
echo "create order"
order_response=$(curl -s -X POST http://localhost:8001/api/orders \
    -H "Content-Type: application/json" \
    -d '{
        "customer_id": "customer_001",
        "product_id": "burger_deluxe",
        "quantity": 2,
        "total_price": 29.99,
        "delivery_address": "123 Tech Street, Baku"
    }')
echo "$order_response" | jq '.'
order_id=$(echo $order_response | jq -r '.data.order_id')
echo "order id $order_id"

echo ""
echo "sim wait payment"
sleep 2
echo "sim wait kitchen"
sleep 3
echo "sim wait delivery"
sleep 4

echo ""
echo "order detail check"
order_details=$(curl -s http://localhost:8001/api/orders/$order_id)
echo "$order_details" | jq '.'

echo ""
echo "extra orders"
for i in {1..3}; do
    echo "order $i"
    curl -s -X POST http://localhost:8001/api/orders \
        -H "Content-Type: application/json" \
        -d "{
            \"customer_id\": \"customer_00$i\",
            \"product_id\": \"burger_$i\",
            \"quantity\": $i,
            \"total_price\": $((15 + i * 5)).99,
            \"delivery_address\": \"$i Tech Street, Baku\"
        }" | jq -r '.data.order_id'
    sleep 1
done

echo ""
echo "rabbit ui http://localhost:15672 guest/guest"
echo "done"
