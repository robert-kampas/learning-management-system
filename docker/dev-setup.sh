#!/bin/bash
set -euo pipefail

echo -e "\033[0;93mApache2 setup...\033[0m"
a2dismod autoindex -f && a2dismod status && a2dismod cgi && a2dismod authz_groupfile && a2dismod alias -f
a2disconf charset && a2disconf localized-error-pages && a2disconf security && a2disconf serve-cgi-bin
a2enmod expires && a2enmod rewrite
passwd -l www-data

echo -e "\033[0;93mInstalling development tools...\033[0m"
curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
apt-get -q update \
  && apt-get -q --assume-yes --no-install-recommends install build-essential \
    openssh-client \
    symfony-cli \
    unzip \
    git \
    libssl-dev \
    libicu-dev \
    libzip-dev \
    libxslt1-dev \
    libxml2-dev \
    pkg-config \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && apt-get -q --assume-yes clean \
  && rm -rf /var/lib/apt/lists/*

echo -e "\033[0;93mDocker PHP extensions setup...\033[0m"
docker-php-ext-configure intl
docker-php-ext-install -j "$(nproc)" \
  mysqli \
  pdo_mysql \
  intl \
  zip \
  xsl

echo -e "\033[0;93mPHP cache directories setup...\033[0m"
mkdir -p /var/php/cache/app/dev /var/php/cache/app/prod
chown -R www-data:www-data /var/php

# Run
exec "$@"
