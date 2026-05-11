FROM php:8.2-apache

# Install system dependencies + build tools
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-dev \
    gcc \
    g++ \
    make \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Install Python dependencies (needs build tools above)
RUN pip3 install --no-cache-dir pymupdf python-docx

# Remove build tools after install (keeps image small)
RUN apt-get purge -y gcc g++ make python3-dev \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# Copy project
COPY . /var/www/html/

RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 80
