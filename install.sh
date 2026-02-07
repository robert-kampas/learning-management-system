#!/bin/bash
set -euo pipefail

# Check Docker
if ! command -v docker &> /dev/null; then
    echo "Error: docker not installed"
    exit 1
fi

# Check docker compose plugin
if ! docker compose version &> /dev/null; then
    echo "Error: docker compose plugin not installed"
    exit 1
fi

docker compose up -d

# Wait for container to be ready
echo "Waiting for container..."
sleep 5

docker exec learning-management-system cp .env.dist .env

echo "Installing dependencies..."
docker exec learning-management-system composer install --optimize-autoloader --no-interaction --prefer-dist

printf "\033[1;32m✓ Install complete\033[0m\n\n"

# Get server IP
CONTAINER_IP=$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' learning-management-system)
printf "\033[1;32mApplication URL: %s\033[0m\n" "http://${CONTAINER_IP}:8889/ or http://0.0.0.0:8889/"
