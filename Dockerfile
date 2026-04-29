FROM php:8.2-apache

# install postgres dependencies
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# copy your project files
COPY . /var/www/html/

# enable apache rewrite if needed
RUN a2enmod rewrite
EXPOSE 80
