# Kubernetes Microservices Deployment

Production-ready Kubernetes deployment for a microservices-based food delivery application. This implementation demonstrates container orchestration, service discovery, autoscaling, and rolling updates.

## Architecture Overview

The application consists of 4 microservices, each with its own MySQL database:

- **Order Service** (3 replicas): Handles order creation and management
- **Payment Service** (5 replicas): Processes payment transactions
- **Kitchen Service** (3 replicas): Manages order preparation queue
- **Delivery Service** (2 replicas): Coordinates delivery assignments

Additional components:
- **MySQL** (4 instances): One database per microservice
- **Redis**: Shared caching layer
- **Ingress Controller**: External access routing

## Prerequisites

- Kubernetes cluster (minikube, kind, or cloud provider)
- kubectl configured
- NGINX Ingress Controller installed
- Metrics Server installed (for HPA)

## Quick Start

### Deploy All Services

```bash
./deploy.sh
```

### Test Deployment

```bash
./test.sh
```

## Directory Structure

```
05-kubernetes/
├── deployments/          Deployment manifests
│   ├── mysql-deployments.yaml
│   ├── redis-deployment.yaml
│   ├── order-deployment.yaml
│   ├── payment-deployment.yaml
│   ├── kitchen-deployment.yaml
│   └── delivery-deployment.yaml
├── services/             Service and ingress manifests
│   ├── mysql-services.yaml
│   ├── order-service.yaml
│   ├── payment-service.yaml
│   ├── kitchen-service.yaml
│   ├── delivery-service.yaml
│   └── ingress.yaml
├── configmaps/           Application configuration
│   └── app-config.yaml
├── hpa/                  Horizontal Pod Autoscaler configs
│   ├── order-hpa.yaml
│   ├── payment-hpa.yaml
│   ├── kitchen-hpa.yaml
│   └── delivery-hpa.yaml
├── deploy.sh             Deployment automation script
├── test.sh               Testing script
└── README.md
```

## Deployment Details

### Resource Configuration

Each microservice has defined resource requests and limits:

```yaml
resources:
  requests:
    memory: 128Mi
    cpu: 100m
  limits:
    memory: 256Mi
    cpu: 500m
```

This ensures predictable resource allocation and prevents resource contention.

### Health Checks

All services implement:

**Liveness Probe**: Detects if container needs restart
```yaml
livenessProbe:
  httpGet:
    path: /health
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 10
  failureThreshold: 3
```

**Readiness Probe**: Determines if pod can receive traffic
```yaml
readinessProbe:
  httpGet:
    path: /health
    port: 80
  initialDelaySeconds: 5
  periodSeconds: 5
  failureThreshold: 2
```

### Rolling Update Strategy

Zero-downtime deployments using rolling updates:

```yaml
strategy:
  type: RollingUpdate
  rollingUpdate:
    maxSurge: 1        # Max extra pods during update
    maxUnavailable: 1   # Max unavailable pods during update
```

Update process:
1. New pod is created (maxSurge)
2. Waits for new pod to be ready
3. Old pod is terminated
4. Repeats until all pods updated

### Horizontal Pod Autoscaling

Each service automatically scales based on CPU and memory usage:

**Order Service HPA**:
- Min replicas: 3
- Max replicas: 10
- CPU target: 60%
- Memory target: 70%

**Payment Service HPA**:
- Min replicas: 5
- Max replicas: 15
- CPU target: 50%
- Memory target: 65%

**Kitchen Service HPA**:
- Min replicas: 3
- Max replicas: 10
- CPU target: 60%
- Memory target: 70%

**Delivery Service HPA**:
- Min replicas: 2
- Max replicas: 8
- CPU target: 70%
- Memory target: 75%

### Scaling Behavior

HPA includes advanced scaling policies:

**Scale Up**: Aggressive to handle traffic spikes
- Can scale up to 100% of current pods every 30 seconds
- Or add 2-3 pods every 30 seconds
- No stabilization window

**Scale Down**: Conservative to prevent flapping
- Can scale down by 50% every 60 seconds
- 5-minute stabilization window

## Service Communication

Services communicate using Kubernetes DNS:

```
http://order-service.microservices.svc.cluster.local
http://payment-service.microservices.svc.cluster.local
http://kitchen-service.microservices.svc.cluster.local
http://delivery-service.microservices.svc.cluster.local
```

Within the same namespace, short names work:
```
http://order-service
http://payment-service
```

## External Access

Services are exposed via NGINX Ingress:

```
http://microservices.local/order
http://microservices.local/payment
http://microservices.local/kitchen
http://microservices.local/delivery
```

Add to `/etc/hosts`:
```
127.0.0.1 microservices.local
```

## Manual Deployment Steps

### 1. Create Namespace

```bash
kubectl create namespace microservices
```

### 2. Apply ConfigMaps

```bash
kubectl apply -f configmaps/ -n microservices
```

### 3. Deploy Databases

```bash
kubectl apply -f deployments/mysql-deployments.yaml -n microservices
kubectl apply -f deployments/redis-deployment.yaml -n microservices
```

### 4. Create Database Services

```bash
kubectl apply -f services/mysql-services.yaml -n microservices
```

### 5. Deploy Microservices

```bash
kubectl apply -f deployments/ -n microservices
```

### 6. Create Application Services

```bash
kubectl apply -f services/ -n microservices
```

### 7. Apply Autoscalers

```bash
kubectl apply -f hpa/ -n microservices
```

## Testing

### Health Check

```bash
kubectl exec -n microservices deployment/order-service -- \
  curl -s http://localhost/health
```

### Create Order

```bash
kubectl exec -n microservices deployment/order-service -- \
  curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"product_id": "prod_123", "quantity": 2, "total_price": 29.99}' \
  http://localhost/api/orders
```

### View Pods

```bash
kubectl get pods -n microservices
```

### View Services

```bash
kubectl get services -n microservices
```

### View HPA Status

```bash
kubectl get hpa -n microservices
```

### View Pod Logs

```bash
kubectl logs -n microservices deployment/order-service
```

## Performing Rolling Update

### Update Image

```bash
kubectl set image deployment/order-service \
  order-service=php:8.3-apache \
  -n microservices
```

### Watch Update Progress

```bash
kubectl rollout status deployment/order-service -n microservices
```

### View Rollout History

```bash
kubectl rollout history deployment/order-service -n microservices
```

### Rollback if Needed

```bash
kubectl rollout undo deployment/order-service -n microservices
```

## Load Testing HPA

Generate load to trigger autoscaling:

```bash
kubectl run -it load-generator --rm --image=busybox --restart=Never \
  -n microservices -- /bin/sh -c \
  "while true; do wget -q -O- http://order-service; done"
```

Watch pods scale:
```bash
kubectl get hpa -n microservices --watch
```

## Monitoring

### Resource Usage

```bash
kubectl top pods -n microservices
kubectl top nodes
```

### Events

```bash
kubectl get events -n microservices --sort-by='.lastTimestamp'
```

### Describe Resources

```bash
kubectl describe pod <pod-name> -n microservices
kubectl describe hpa order-service-hpa -n microservices
```

## Cleanup

```bash
kubectl delete namespace microservices
```

## Production Considerations

### Security
- Use Kubernetes Secrets for sensitive data instead of plain ConfigMaps
- Implement NetworkPolicies to restrict pod-to-pod communication
- Enable RBAC and limit service account permissions
- Use Pod Security Standards/Policies
- Scan container images for vulnerabilities

### High Availability
- Run multiple replicas across different nodes
- Use Pod Anti-Affinity to spread pods
- Configure PodDisruptionBudgets to ensure minimum availability during maintenance
- Use StatefulSets for databases with proper replication

### Storage
- Use StorageClasses with appropriate provisioners
- Implement backup strategies for PersistentVolumes
- Consider using managed database services for production

### Observability
- Integrate with Prometheus for metrics collection
- Use Grafana for visualization
- Implement distributed tracing with Jaeger or Zipkin
- Centralize logs with ELK stack or Loki

### Performance
- Configure resource requests/limits based on actual usage
- Tune HPA metrics and thresholds
- Use node affinity to optimize pod placement
- Consider cluster autoscaling for dynamic workloads

### Networking
- Use TLS for Ingress with cert-manager
- Implement service mesh (Istio/Linkerd) for advanced traffic management
- Configure proper DNS caching
- Use CDN for static content

### Configuration Management
- Use Helm charts for templating and versioning
- Implement GitOps with ArgoCD or Flux
- Separate configurations per environment
- Use Kustomize for environment-specific overlays

## Best Practices Implemented

1. **Declarative Configuration**: All resources defined in YAML
2. **Resource Limits**: Prevents resource starvation
3. **Health Checks**: Automated failure detection and recovery
4. **Rolling Updates**: Zero-downtime deployments
5. **Autoscaling**: Dynamic capacity management
6. **Service Discovery**: Built-in DNS-based discovery
7. **Persistent Storage**: Data survives pod restarts
8. **Namespace Isolation**: Logical separation of resources
9. **Labels and Selectors**: Organized resource management
10. **Ingress Controller**: Centralized external access

## Troubleshooting

### Pods Not Starting

```bash
kubectl describe pod <pod-name> -n microservices
kubectl logs <pod-name> -n microservices
```

### Service Not Reachable

```bash
kubectl get endpoints -n microservices
kubectl run debug --rm -it --image=busybox --restart=Never -n microservices -- wget -O- http://order-service
```

### HPA Not Scaling

```bash
kubectl describe hpa order-service-hpa -n microservices
kubectl top pods -n microservices
```

Ensure Metrics Server is installed:
```bash
kubectl get deployment metrics-server -n kube-system
```

### Database Connection Issues

```bash
kubectl exec -it <mysql-pod-name> -n microservices -- mysql -uroot -proot
kubectl logs <app-pod-name> -n microservices
```

## Additional Resources

- [Kubernetes Documentation](https://kubernetes.io/docs/)
- [HPA Documentation](https://kubernetes.io/docs/tasks/run-application/horizontal-pod-autoscale/)
- [Ingress Documentation](https://kubernetes.io/docs/concepts/services-networking/ingress/)
- [Best Practices](https://kubernetes.io/docs/concepts/configuration/overview/)
