# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
# We use npm install because package-lock.json might not exist or be perfectly synced
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Build composer dependencies
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs
COPY . .

# Stage 3: Final PHP+Apache Image
FROM php:8.2-apache
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache DocumentRoot to public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application code first
COPY . /var/www/html/

# Then overlay the built artifacts from previous stages
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Ensure proper permissions for Laravel cache/storage directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Note: In production you may also want to run route/config caching.
# We will run this script as the default entrypoint or command if needed, 
# but for now apache2-foreground handles serving.
EXPOSE 80
CMD ["apache2-foreground"]
