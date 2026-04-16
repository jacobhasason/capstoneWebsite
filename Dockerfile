FROM php:8.2-apache

# Copy all files into Apache's root directory
COPY . /var/www/html/

# Set working directory (helps with relative paths)
WORKDIR /var/www/html

# Enable rewrite module (useful if you add routing later)
RUN a2enmod rewrite

# Fix permissions (prevents weird access bugs)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
