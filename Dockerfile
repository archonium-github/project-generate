# Use an official PHP runtime as a parent image
FROM php:8.2-fpm

# Set the working directory in the container
WORKDIR /var/www

# Copy the current directory contents into the container at /var/www
COPY . .

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy Supervisor configuration
COPY ./supervisor/laravel-worker.conf /etc/supervisor/conf.d/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Set php memory limit
# RUN echo "memory_limit = 1024M" >> /usr/local/etc/php/conf.d/memory-limit.ini

# Expose port 3000
EXPOSE 3000

# Start the PHP FastCGI Process Manager
CMD ["php-fpm"]
