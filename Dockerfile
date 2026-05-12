FROM php:8.2-apache

# Install system dependencies (IMPORTANT for PyMuPDF)
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-dev \
    gcc \
    g++ \
    make \
    libmupdf-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Install Python dependencies
RUN python3 -m pip install --upgrade pip && \
    pip3 install --no-cache-dir \
    pymupdf \
    python-docx

# Copy project into container
COPY . /var/www/html/

# Enable Apache rewrite (safe to keep)
RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 80
