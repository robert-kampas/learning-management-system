FROM php:8.4-apache-bookworm

ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_LOG_DIR=/var/log/apache2/
ENV APACHE_PID_FILE=/var/run/apache2.pid
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_LOCK_DIR=/var/lock/apache2

# setting up the development container
COPY ./docker/dev-setup.sh /usr/local/bin/dev-setup.sh
RUN chmod +x /usr/local/bin/dev-setup.sh
RUN ["/usr/local/bin/dev-setup.sh"]

COPY ./docker/apache/000-default.conf /etc/apache2/sites-available
COPY ./docker/apache/conf-available /etc/apache2/conf-available/
RUN a2enconf x-99-custom
COPY ./docker/php.ini /usr/local/etc/php/
RUN rm -rf /var/www/html
WORKDIR /var/www

# docker build -t learning-management-system .