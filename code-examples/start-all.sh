#!/bin/bash

echo "Spinning up PlovExpress demos"

if ! command -v docker >/dev/null 2>&1; then
  echo "docker cli missing"
  exit 1
fi

if ! docker info >/dev/null 2>&1; then
  echo "docker daemon not reachable"
  exit 1
fi

if docker compose version >/dev/null 2>&1; then
  compose_cmd() { docker compose "$@"; }
elif command -v docker-compose >/dev/null 2>&1; then
  compose_cmd() { docker-compose "$@"; }
else
  echo "docker compose command missing"
  exit 1
fi

apps=(monolith-classic monolith-scaled microservices-suite)
labels=("monolith-classic -> 8000" "monolith-scaled -> 8001" "microservices-suite -> 8002-8005")
started=()
failed=()

for i in "${!apps[@]}"; do
  echo ""
  echo "${labels[$i]}"
  if (cd "${apps[$i]}" && compose_cmd up -d); then
    started+=("${apps[$i]}")
  else
    failed+=("${apps[$i]}")
  fi
done

if [ "${#failed[@]}" -gt 0 ]; then
  echo ""
  echo "failed to start:"
  for svc in "${failed[@]}"; do
    echo "  $svc"
  done
  exit 1
fi

echo ""
echo "letting containers settle"
sleep 10

echo ""
echo "ready to poke:"
echo "  monolith-classic      http://localhost:8000"
echo "  monolith-scaled       http://localhost:8001"
echo "  microservices-suite"
echo "    order-service       http://localhost:8005"
echo "    payment-service     http://localhost:8002"
echo "    kitchen-service     http://localhost:8003"
echo "    delivery-service    http://localhost:8004"
echo ""
echo "kick './run-all.sh' when you want checks"
