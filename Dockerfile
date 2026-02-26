FROM php:8.5-cli

# Set environment variables
ENV APP_ENV=prod

# Install system deps
RUN apt-get update && apt-get install -y \
    build-essential \
    git \
    libicu-dev \
    libonig-dev \
    libsqlite3-dev \
    supervisor \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install intl mbstring pdo pdo_sqlite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project
COPY . .

# Install dependencies
RUN composer install -a -o --no-dev

# Create Symfony writable dirs
RUN mkdir -p var/cache var/log && chmod -R 777 var

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8844

ENTRYPOINT ["/entrypoint.sh"]
