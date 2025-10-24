# Scalable PHP Applications - From Monolith to Kubernetes Microservices

Code examples for PHPKonf 2025 Baku presentation on scaling PHP applications.

## Overview

This repository contains practical code examples demonstrating the evolution of a PHP application from a simple monolith to a fully-scaled microservices architecture running on Kubernetes.

**Business Case:** PlovExpress - A food delivery service in Baku

## Examples Structure

### 1. Monolit Architecture
**Path:** `01-monolit-architecture/`

Simple Laravel monolithic application where all functionality lives in one codebase.

**Characteristics:**
- Single deployment unit
- Shared database
- Vertical scaling only
- 20-30 orders/day capacity

**Use Case:** Starting a new project, MVP, small applications

[View Example →](01-monolit-architecture/)

---

### 2. Scalable Monolith
**Path:** `02-scalable-monolith/`

Same monolith but with horizontal scaling using load balancer and multiple instances.

**Key Features:**
- NGINX load balancer
- 3 Laravel instances
- Redis for shared sessions
- MySQL shared database
- 100+ orders/day capacity

**Use Case:** Growing applications with increasing traffic

[View Example →](02-scalable-monolith/)

---

### 3. Modular Monolith
**Path:** `03-modular-monolith/`

Monolith organized into clear modules with defined boundaries and interfaces.

**Architecture:**
- Domain-Driven Design (DDD)
- Separated modules (Order, Payment, Kitchen, Delivery)
- Interface-based communication
- Preparation for microservices

**Use Case:** Medium-sized apps, preparing for future microservices

[View Example →](03-modular-monolith/)

---

### 4. Microservices Architecture
**Path:** `04-microservices/`

Full microservices with independent services communicating via HTTP.

**Services:**
- **Order Service** (Symfony) - Port 8001
- **Payment Service** (Laravel) - Port 8002
- **Kitchen Service** (Laravel) - Port 8003
- **Delivery Service** (Laravel) - Port 8004

**Benefits:**
- Independent scaling
- Independent deployment
- Technology freedom
- Team autonomy

**Use Case:** Large applications, multiple teams, different scaling needs

[View Example →](04-microservices/)

---

### 5. Kubernetes Deployment
**Path:** `05-kubernetes/`

Microservices deployed on Kubernetes with auto-scaling and zero-downtime updates.

**Features:**
- Horizontal Pod Autoscaler (HPA)
- Self-healing
- Load balancing
- Rolling updates
- Service discovery

**Configuration:**
- Deployments
- Services
- Ingress
- ConfigMaps & Secrets
- HPA

**Use Case:** Production-ready scalable applications

[View Example →](05-kubernetes/)

---

### 6. Event-Driven Architecture
**Path:** `06-event-driven/`

Microservices with asynchronous communication using RabbitMQ.

**Components:**
- Event producers
- Event consumers
- RabbitMQ message broker
- Dead letter queues
- Event replay capability

**Benefits:**
- Loose coupling
- Better resilience
- Easy to scale consumers
- SAGA pattern for distributed transactions

**Use Case:** Complex workflows, high-volume processing, eventual consistency

[View Example →](06-event-driven/)

---

## Quick Start

### Prerequisites
- Docker & Docker Compose
- PHP 8.2+
- Composer
- kubectl (for Kubernetes examples)

### Running Examples

#### Monolith
```bash
cd 01-monolit-architecture
composer install
php artisan serve
```

#### Scalable Monolith
```bash
cd 02-scalable-monolith
docker-compose up -d
```

#### Microservices
```bash
cd 04-microservices
docker-compose up -d
```

#### Kubernetes
```bash
cd 05-kubernetes
kubectl apply -f deployments/
kubectl apply -f services/
kubectl apply -f hpa/
```

#### Event-Driven
```bash
cd 06-event-driven
docker-compose up -d
```

## Architecture Comparison

| Feature | Monolith | Scalable Monolith | Modular Monolith | Microservices | Kubernetes |
|---------|----------|-------------------|------------------|---------------|------------|
| Deployment | Single | Multiple instances | Single | Independent | Orchestrated |
| Scaling | Vertical | Horizontal | Vertical | Per service | Auto-scaling |
| Complexity | Low | Medium | Medium | High | Very High |
| Team Size | 1-5 | 5-10 | 5-15 | 15+ | 20+ |
| Database | Single | Single | Single | Per service | Per service |
| Development Speed | Fast | Fast | Medium | Slower | Slower |
| Operational Cost | Low | Medium | Low | High | Very High |
| Fault Isolation | None | Partial | None | Full | Full |
| Technology Freedom | None | None | Limited | Full | Full |

## When to Use Each Pattern

### Use Monolith When:
- Starting new project
- Small team (< 5 people)
- Simple domain
- Limited traffic
- MVP/Prototype

### Use Scalable Monolith When:
- Traffic increasing
- Need better availability
- Same codebase works well
- Team still manageable
- Cost-conscious

### Use Modular Monolith When:
- Medium-sized application
- Clear domain boundaries
- Preparing for microservices
- Want better organization
- Single team but growing

### Use Microservices When:
- Large application
- Multiple teams
- Different scaling needs per module
- Need technology diversity
- Complex domain

### Use Kubernetes When:
- Production microservices
- Need auto-scaling
- High availability required
- Multiple environments
- DevOps maturity

### Use Event-Driven When:
- Async workflows
- High-volume processing
- Complex business processes
- Eventual consistency acceptable
- Need event replay

## Key Concepts

### Vertical Scaling
Adding more CPU/RAM to existing server.
- **Pros:** Simple, no code changes
- **Cons:** Hardware limits, expensive, single point of failure

### Horizontal Scaling
Adding more server instances.
- **Pros:** Unlimited scaling, cost-effective
- **Cons:** Session management, database bottleneck

### Service Discovery
Services finding each other automatically.
- DNS-based
- Service registry (Consul, Eureka)
- Kubernetes built-in

### Load Balancing
Distributing traffic across instances.
- Round-robin
- Least connections
- IP hash

### Circuit Breaker
Preventing cascading failures.
```php
try {
    $response = $this->httpClient->request(...);
} catch (Exception $e) {
    return $this->fallbackResponse();
}
```

### Health Checks
Monitoring service health.
- Liveness: Is service alive?
- Readiness: Can service handle traffic?

## Best Practices

### 1. Start Simple
Don't start with microservices. Begin with monolith and evolve.

### 2. Monitor Everything
- Application metrics
- Infrastructure metrics
- Business metrics
- Logs aggregation

### 3. Automate Deployment
- CI/CD pipelines
- Automated testing
- Rollback strategy

### 4. Plan for Failure
- Retry logic
- Timeouts
- Circuit breakers
- Graceful degradation

### 5. Security
- HTTPS everywhere
- Secret management
- RBAC
- Network policies

### 6. Database Strategy
- Connection pooling
- Read replicas
- Caching layer
- Database per service (microservices)

### 7. Observability
- Structured logging
- Distributed tracing
- Centralized monitoring
- Alert management

## Technologies Used

### Frameworks
- Laravel 11
- Symfony 7

### Infrastructure
- Docker
- Docker Compose
- Kubernetes
- NGINX

### Databases
- MySQL 8.0
- Redis

### Message Brokers
- RabbitMQ

### Monitoring (mentioned)
- Prometheus
- Grafana
- Jaeger
- ELK Stack

## Learning Path

1. **Week 1-2:** Build and understand monolith
2. **Week 3:** Scale monolith horizontally
3. **Week 4:** Refactor to modular monolith
4. **Week 5-6:** Extract first microservice
5. **Week 7-8:** Deploy to Kubernetes
6. **Week 9:** Implement event-driven patterns

## Common Pitfalls

### 1. Premature Microservices
Starting with microservices too early adds unnecessary complexity.

### 2. Distributed Monolith
Microservices with tight coupling - worst of both worlds.

### 3. Ignoring Observability
Can't debug what you can't see. Invest in logging/monitoring early.

### 4. No Service Boundaries
Services should own their data and have clear boundaries.

### 5. Synchronous Everything
Too many synchronous calls create tight coupling and cascading failures.

## Resources

### Books
- "Building Microservices" by Sam Newman
- "Domain-Driven Design" by Eric Evans
- "Release It!" by Michael Nygard

### Online
- Kubernetes Documentation: https://kubernetes.io/docs/
- Laravel Documentation: https://laravel.com/docs
- Symfony Documentation: https://symfony.com/doc
- RabbitMQ Tutorials: https://www.rabbitmq.com/tutorials

## Contributing

This is an educational repository for PHPKonf 2025 Baku.

## License

MIT License - free to use for learning purposes.

## Contact

For questions about these examples or the presentation:
- GitHub: [Your GitHub]
- Email: [Your Email]
- Twitter: [Your Twitter]

---

**PHPKonf 2025 Baku** | **Scalable PHP Applications** | **From Monolith to Kubernetes Microservices**
