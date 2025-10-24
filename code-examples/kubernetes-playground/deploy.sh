#!/bin/bash

set -e

echo "start kube deploy"
echo "namespace setup"
kubectl create namespace microservices --dry-run=client -o yaml | kubectl apply -f -

echo "configmaps"
kubectl apply -f configmaps/ -n microservices

echo "mysql deploy"
kubectl apply -f deployments/mysql-deployments.yaml -n microservices

echo "redis deploy"
kubectl apply -f deployments/redis-deployment.yaml -n microservices

echo "wait data layer"
kubectl wait --for=condition=ready pod -l app=mysql-order -n microservices --timeout=180s
kubectl wait --for=condition=ready pod -l app=mysql-payment -n microservices --timeout=180s
kubectl wait --for=condition=ready pod -l app=mysql-kitchen -n microservices --timeout=180s
kubectl wait --for=condition=ready pod -l app=mysql-delivery -n microservices --timeout=180s
kubectl wait --for=condition=ready pod -l app=redis -n microservices --timeout=180s

echo "svc for db cache"
kubectl apply -f services/mysql-services.yaml -n microservices

echo "apps deploy"
kubectl apply -f deployments/order-deployment.yaml -n microservices
kubectl apply -f deployments/payment-deployment.yaml -n microservices
kubectl apply -f deployments/kitchen-deployment.yaml -n microservices
kubectl apply -f deployments/delivery-deployment.yaml -n microservices

echo "svc for apps"
kubectl apply -f services/order-service.yaml -n microservices
kubectl apply -f services/payment-service.yaml -n microservices
kubectl apply -f services/kitchen-service.yaml -n microservices
kubectl apply -f services/delivery-service.yaml -n microservices

echo "wait app pods"
kubectl wait --for=condition=ready pod -l app=order-service -n microservices --timeout=120s
kubectl wait --for=condition=ready pod -l app=payment-service -n microservices --timeout=120s
kubectl wait --for=condition=ready pod -l app=kitchen-service -n microservices --timeout=120s
kubectl wait --for=condition=ready pod -l app=delivery-service -n microservices --timeout=120s

echo "ingress apply"
kubectl apply -f services/ingress.yaml -n microservices

echo "hpa apply"
kubectl apply -f hpa/ -n microservices

echo ""
echo "deploy done"
echo ""
echo "pods:"
kubectl get pods -n microservices
echo ""
echo "services:"
kubectl get services -n microservices
echo ""
echo "hpa:"
kubectl get hpa -n microservices
echo ""
echo "remember hosts entry 127.0.0.1 microservices.local"
echo "urls:"
echo "  order http://microservices.local/order"
echo "  payment http://microservices.local/payment"
echo "  kitchen http://microservices.local/kitchen"
echo "  delivery http://microservices.local/delivery"
