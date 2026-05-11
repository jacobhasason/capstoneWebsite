FROM php:8.2-apache

# Install ONLY required system + Python runtime
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install ONLY Python libraries your script actually uses
RUN pip3 install --no-cache-dir \
    pymupdf \
    python-docx

# Copy project
COPY . /var/www/html/

# Enable rewrite (optional but safe)
RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 80
