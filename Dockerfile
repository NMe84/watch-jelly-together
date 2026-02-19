FROM php:8.5-cli

# Install system deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    supervisor \
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

EXPOSE 8844

CMD ["/usr/bin/supervisord", "-n"]
