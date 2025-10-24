#!/bin/bash

echo "Shutting things down"

if docker compose version >/dev/null 2>&1; then
  compose_cmd() { docker compose "$@"; }
elif command -v docker-compose >/dev/null 2>&1; then
  compose_cmd() { docker-compose "$@"; }
else
  echo "docker compose command missing"
  exit 1
fi

(cd monolith-classic && compose_cmd down)
(cd monolith-scaled && compose_cmd down)
(cd microservices-suite && compose_cmd down)

echo "Done"
