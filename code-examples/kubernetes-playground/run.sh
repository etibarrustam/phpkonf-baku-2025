#!/bin/bash

set -e

NAMESPACE="microservices"

echo "kube smoke run"
echo ""

echo "health endpoints"
for svc in order payment kitchen delivery; do
  echo "$svc service"
  kubectl exec -n "$NAMESPACE" deployment/"$svc"-service -- curl -s http://localhost/health | grep -q "healthy" && echo "  ok" || echo "  fail"
done

echo ""
echo "mysql ping"
for db in order payment kitchen delivery; do
  echo "$db db"
  kubectl exec -n "$NAMESPACE" deployment/mysql-"$db" -- mysqladmin -uroot -proot ping | grep -q "alive" && echo "  ok" || echo "  fail"
done

echo ""
echo "redis ping"
kubectl exec -n "$NAMESPACE" deployment/redis -- redis-cli ping | grep -q "PONG" && echo "  ok" || echo "  fail"

echo ""
echo "order flow call"
ORDER_JSON='{"product_id": "prod_123", "quantity": 2, "total_price": 29.99}'
RESULT=$(kubectl exec -n "$NAMESPACE" deployment/order-service -- curl -s -X POST \
  -H "Content-Type: application/json" \
  -d "$ORDER_JSON" \
  http://localhost/api/orders)

echo "$RESULT" | grep -q "success" && echo "  success" || echo "  fail"

ORDER_ID=$(echo "$RESULT" | grep -o '"order_id":"[^"]*"' | cut -d'"' -f4)

if [ -n "$ORDER_ID" ]; then
  echo "check order $ORDER_ID"
  kubectl exec -n "$NAMESPACE" deployment/order-service -- curl -s \
    http://localhost/api/orders/$ORDER_ID | grep -q "success" && echo "  ok" || echo "  fail"
fi

echo ""
echo "hpa list"
kubectl get hpa -n "$NAMESPACE"

echo ""
echo "pods wide"
kubectl get pods -n "$NAMESPACE" -o wide

echo ""
echo "order image"
kubectl get deployment order-service -n "$NAMESPACE" -o jsonpath='{.spec.template.spec.containers[0].image}'
echo ""

echo "done"
