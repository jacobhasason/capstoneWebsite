FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    python3 \
    python3-venv \
    python3-dev \
    gcc \
    g++ \
    make \
    libmupdf-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql


RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "output_buffering = 4096" >> "$PHP_INI_DIR/php.ini"


RUN python3 -m venv /venv


RUN /venv/bin/pip install --no-cache-dir pymupdf python-docx


ENV PATH="/venv/bin:$PATH"

COPY . /var/www/html/

RUN a2enmod rewrite
