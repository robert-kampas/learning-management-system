#!/bin/bash
set -euo pipefail

docker exec learning-management-system bin/console doctrine:schema:update --force -n
docker exec learning-management-system php bin/console doctrine:fixtures:load -n

printf "\n\033[36mCourse report URLs:\033[0m\n"
docker exec learning-management-system bin/console app:list-course-report-urls

printf "\n\033[36mEnrollment certificate URLs:\033[0m\n"
docker exec learning-management-system bin/console app:list-enrollment-certificate-urls

printf "\n\033[1;32m✓ Setup complete\033[0m\n"
