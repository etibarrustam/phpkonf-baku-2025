# Microservices Architecture

This example demonstrates a complete microservices architecture with 4 independent services that communicate via HTTP.

## Architecture Overview

```
┌─────────────────┐
│  Order Service  │ (Symfony 7 - Port 8001)
│  (Orchestrator) │
└────────┬────────┘
         │
         ├──────────────┐
         │              │
    ┌────▼─────┐   ┌───▼────────┐
    │ Payment  │   │  Kitchen   │
    │ Service  │   │  Service   │
    │ (Laravel)│   │ (Laravel)  │
    │ Port 8002│   │ Port 8003  │
    └──────────┘   └────┬───────┘
                        │
                   ┌────▼────────┐
                   │  Delivery   │
                   │  Service    │
                   │  (Laravel)  │
                   │  Port 8004  │
                   └─────────────┘
```

## Services

### 1. Order Service (Symfony 7 - Port 8001)
- Main orchestrator service
- Manages order lifecycle
- Communicates with all other services
- Database: order_service (MySQL)
- Endpoints:
  - GET /health
  - POST /orders
  - GET /orders
  - GET /orders/{id}

### 2. Payment Service (Laravel 11 - Port 8002)
- Processes payments
- Manages payment transactions
- Database: payment_service (MySQL)
- Endpoints:
  - GET /health
  - POST /payments
  - GET /payments
  - GET /payments/{id}

### 3. Kitchen Service (Laravel 11 - Port 8003)
- Manages food preparation
- Tracks kitchen orders
- Database: kitchen_service (MySQL)
- Endpoints:
  - GET /health
  - POST /kitchen/prepare
  - GET /kitchen
  - GET /kitchen/{id}
  - PATCH /kitchen/{id}/status

### 4. Delivery Service (Laravel 11 - Port 8004)
- Assigns delivery drivers
- Tracks deliveries
- Database: delivery_service (MySQL)
- Endpoints:
  - GET /health
  - POST /deliveries
  - GET /deliveries
  - GET /deliveries/{id}
  - PATCH /deliveries/{id}/status

## Key Features

### Service Independence
- Each service has its own database
- Services can be deployed independently
- Failure in one service doesn't crash others
- Each service can scale independently

### HTTP Communication
- Services communicate via REST API
- Uses Guzzle HTTP client for inter-service calls
- Synchronous request/response pattern
- Clear API contracts between services

### Database Per Service
- order_service: Stores orders
- payment_service: Stores payments
- kitchen_service: Stores kitchen orders
- delivery_service: Stores delivery information

## Order Flow

1. Client creates order via Order Service
2. Order Service calls Payment Service to process payment
3. Order Service calls Kitchen Service to prepare food
4. Order Service calls Delivery Service to assign driver
5. Each service updates its own database
6. Order Service returns complete order with IDs from all services

## Setup

```bash
docker-compose up -d
```

Wait for all services to be healthy, then run migrations:

```bash
docker-compose exec order-service php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec payment-service php artisan migrate --force
docker-compose exec kitchen-service php artisan migrate --force
docker-compose exec delivery-service php artisan migrate --force
```

## Testing

Run the test script:

```bash
chmod +x test.sh
./test.sh
```

Or test manually:

```bash
curl -X POST http://localhost:8001/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "items": ["Pizza", "Coke"],
    "total_amount": "25.50"
  }'
```

## Advantages

1. **Independent Deployment**: Deploy services separately
2. **Technology Diversity**: Different frameworks per service
3. **Scalability**: Scale services based on load
4. **Fault Isolation**: Service failures are isolated
5. **Team Autonomy**: Different teams can own different services

## Disadvantages

1. **Complexity**: More services to manage
2. **Network Latency**: HTTP calls add overhead
3. **Data Consistency**: Distributed transactions are hard
4. **Testing**: Integration testing is complex
5. **Monitoring**: Need to track multiple services

## Best Practices Implemented

1. **Health Checks**: Each service has /health endpoint
2. **Error Handling**: Services handle communication failures
3. **Database Isolation**: No shared databases
4. **API Versioning**: Clear API contracts
5. **Logging**: Each service logs independently
6. **Container Orchestration**: Docker Compose manages all services

## When to Use Microservices

- Large teams working on different features
- Need to scale services independently
- Different performance requirements per service
- Want to use different technologies
- Need high availability and fault tolerance

## When NOT to Use Microservices

- Small applications with simple requirements
- Limited team size
- Tight budget for infrastructure
- Need for distributed transactions
- Simple CRUD operations

## Production Considerations

1. **Service Discovery**: Use Consul, Eureka, or Kubernetes
2. **API Gateway**: Add Kong, Nginx, or AWS API Gateway
3. **Load Balancing**: Distribute traffic across instances
4. **Monitoring**: Use Prometheus, Grafana, ELK stack
5. **Tracing**: Implement distributed tracing (Jaeger, Zipkin)
6. **Circuit Breaker**: Prevent cascade failures
7. **Message Queue**: Consider async communication (RabbitMQ, Kafka)
8. **Container Orchestration**: Use Kubernetes in production
