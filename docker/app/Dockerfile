# Use PHP 8.2 with Apache as our base image
# This gives us PHP with a web server already configured
FROM php:8.2-apache

# Set working directory inside the container
# All our application code will live here
WORKDIR /var/www/html

# Install system dependencies that Laravel needs
# These are Ubuntu packages that PHP extensions require
RUN apt-get update && apt-get install -y \
    # Git for Composer to download packages
    git \
    # curl for making HTTP requests
    curl \
    # libpng-dev for image processing (user avatars, receipts)
    libpng-dev \
    # libonig-dev for mbstring (multi-byte string handling)
    libonig-dev \
    # libxml2-dev for XML processing
    libxml2-dev \
    # zip/unzip for Composer package management
    zip \
    unzip \
    # supervisor to run multiple processes (Laravel queues, etc.)
    supervisor \
    # Clean up apt cache to reduce image size
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions that Laravel requires
# These extend PHP's functionality for web applications
RUN docker-php-ext-install \
    # PDO MySQL for database connections
    pdo_mysql \
    # mbstring for handling UTF-8 strings (international characters)
    mbstring \
    # exif for reading image metadata (useful for receipt uploads)
    exif \
    # pcntl for process control (Laravel queues)
    pcntl \
    # bcmath for precise decimal calculations (important for financial data!)
    bcmath \
    # gd for image processing (resize uploaded receipts)
    gd

# Install Composer (PHP package manager)
# Composer is like npm for PHP - it manages our dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm for frontend asset compilation
# We need this to compile our Vue.js frontend and Tailwind CSS
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Copy supervisor configuration
# Supervisor will manage background processes like Laravel queues
COPY docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Enable Apache rewrite module (required for Laravel's pretty URLs)
# This allows routes like /dashboard instead of /index.php/dashboard
RUN a2enmod rewrite

# Set proper permissions for Apache
# The web server needs to read our files and write to storage directories
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for HTTP traffic
# This tells Docker that our container listens on port 80
EXPOSE 80

# Start supervisor (which will start Apache and other services)
# This keeps our container running and manages all processes
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]