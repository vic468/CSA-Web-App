# Use official PHP 8.2 Apache image
FROM php:8.2-apache

# Set environment variables
ENV APACHE_DOCUMENT_ROOT /var/www/html
ENV PHP_MEMORY_LIMIT 256M
ENV PHP_MAX_EXECUTION_TIME 30
ENV PHP_UPLOAD_MAX_FILESIZE 10M
ENV PHP_POST_MAX_SIZE 10M

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libsodium-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    intl \
    mbstring \
    xml \
    opcache \
    && docker-php-ext-enable \
    sodium

# Configure Apache
RUN a2enmod rewrite headers ssl

# Set Apache document root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure PHP
RUN echo "memory_limit = ${PHP_MEMORY_LIMIT}" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = ${PHP_MAX_EXECUTION_TIME}" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = ${PHP_POST_MAX_SIZE}" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "session.cookie_httponly = 1" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "session.cookie_secure = 1" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "session.use_strict_mode = 1" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "session.cookie_samesite = Strict" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "expose_php = Off" >> /usr/local/etc/php/conf.d/custom.ini

# Configure OPcache for better performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Create application directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/logs \
    && chmod -R 777 /var/www/html/uploads

# Create logs directory
RUN mkdir -p /var/www/html/logs && chmod 777 /var/www/html/logs

# Create uploads directory
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Create .htaccess for security
RUN echo "Options -Indexes" > /var/www/html/.htaccess \
    && echo "ServerTokens Prod" >> /var/www/html/.htaccess \
    && echo "ServerSignature Off" >> /var/www/html/.htaccess \
    && echo "Header always set X-Content-Type-Options nosniff" >> /var/www/html/.htaccess \
    && echo "Header always set X-Frame-Options DENY" >> /var/www/html/.htaccess \
    && echo "Header always set X-XSS-Protection \"1; mode=block\"" >> /var/www/html/.htaccess \
    && echo "Header always set Referrer-Policy \"strict-origin-when-cross-origin\"" >> /var/www/html/.htaccess \
    && echo "Header always set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; font-src 'self' cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self'\"" >> /var/www/html/.htaccess

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache
CMD ["apache2-foreground"]
