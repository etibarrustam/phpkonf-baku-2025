# Scalable Monolith - PlovExpress

Laravel application with horizontal scaling using load balancer, multiple instances, and Redis for shared state.

## Features

- **Laravel 11** framework
- **NGINX** load balancer (round-robin)
- **3 Laravel instances** running in parallel
- **Redis** for session and cache storage
- **MySQL** shared database
- All instances share the same codebase and data

## Architecture

```
                    ┌─────────────┐
                    │   NGINX     │
                    │Load Balancer│
                    └──────┬──────┘
                           │
         ┌─────────────────┼────────────────┐
         │                 │                │
    ┌────▼────┐      ┌─────▼────┐    ┌─────▼────┐
    │  App 1  │      │  App 2   │    │  App 3   │
    └────┬────┘      └─────┬────┘    └─────┬────┘
         │                 │                │
         └─────────────────┼────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
         ┌────▼────┐              ┌─────▼────┐
         │  MySQL  │              │  Redis   │
         └─────────┘              └──────────┘
```

## Running

```bash
docker-compose up -d
```

Access: http://localhost:8001

## Testing

```bash
./test.sh
```

## Key Improvements

1. **Session Management**: Redis-based sessions work across all instances
2. **Caching**: Shared cache layer prevents stale data
3. **Load Balancing**: Traffic distributed evenly
4. **Horizontal Scalability**: Add more instances easily

## Capacity

- Handles 100+ orders/day
- Better fault tolerance
- No single point of failure (except LB)
