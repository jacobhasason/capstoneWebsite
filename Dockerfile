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

# Create virtual environment
RUN python3 -m venv /venv

# Install Python packages inside venv
RUN /venv/bin/pip install --no-cache-dir pymupdf python-docx

# Make venv default python
ENV PATH="/venv/bin:$PATH"

COPY . /var/www/html/

RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 80
