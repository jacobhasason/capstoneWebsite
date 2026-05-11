FROM php:8.2-apache

# Install system dependencies + Python
RUN apt-get update && apt-get install -y \
    libpq-dev \
    python3 \
    python3-pip \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Python libraries used by your scraper
RUN pip3 install --no-cache-dir \
    pymupdf \
    python-docx \
    requests \
    beautifulsoup4

# Copy project files
COPY . /var/www/html/

# Enable Apache rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 80
