# Use a PHP base image
FROM php:8.1-apache

# Set the working directory
WORKDIR /var/www/html

# Copy project files into the container
COPY index.php .

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
