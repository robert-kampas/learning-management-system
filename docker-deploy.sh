#!/bin/bash
set -euo pipefail

# Configuration
IMAGE_NAME="learning-management-system"
DOCKER_USERNAME="rkampas"
VERSION="${1:-latest}"

echo "Building image..."
docker build -t "${IMAGE_NAME}:${VERSION}" .

echo "Tagging image..."
docker tag "${IMAGE_NAME}:${VERSION}" "${DOCKER_USERNAME}/${IMAGE_NAME}:${VERSION}"
docker tag "${IMAGE_NAME}:${VERSION}" "${DOCKER_USERNAME}/${IMAGE_NAME}:latest"

echo "Logging into Docker Hub..."
echo "${DOCKER_HUB_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin

echo "Pushing image..."
docker push "${DOCKER_USERNAME}/${IMAGE_NAME}:${VERSION}"
docker push "${DOCKER_USERNAME}/${IMAGE_NAME}:latest"

echo "✓ Deployed ${DOCKER_USERNAME}/${IMAGE_NAME}:${VERSION}"
