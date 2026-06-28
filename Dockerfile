FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Ganti composer install → dump-autoload karena vendor sudah ada
RUN composer dump-autoload --optimize --no-dev

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate:fresh --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
