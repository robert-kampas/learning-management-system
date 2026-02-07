#!/bin/bash
set -euo pipefail

docker exec learning-management-system bin/console doctrine:schema:update --force -n
docker exec learning-management-system php bin/console doctrine:fixtures:load -n

printf "\n\033[1;32m✓ Setup complete\033[0m\n"
