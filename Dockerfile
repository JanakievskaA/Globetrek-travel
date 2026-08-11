# Render has no native PHP runtime, so the app ships to it as a container.
#
# php:8.4-apache rather than php-fpm + a separate nginx: Apache serves public/
# on its own, which keeps this to one process and no supervisor. Sail's compose
# file next door is still the local story — this image is production only.
FROM php:8.4-apache

# unzip is what Composer shells out to for dist packages, which is why the zip
# extension is not installed. pdo_sqlite and sqlite3 are already compiled into
# the official image, so the database needs nothing added here.
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# The one extension worth adding. Free instances are small and start cold, and
# precompiled bytecode is most of the difference on the first request after a
# spin-up.
RUN docker-php-ext-install -j"$(nproc)" opcache

COPY docker/php.ini /usr/local/etc/php/conf.d/globetrek.ini

# mod_rewrite backs public/.htaccess, which routes everything to index.php.
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencies before the app, so this layer is rebuilt only when the lock file
# moves. Scripts and the autoloader are deferred because both want app/ present.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .

# Now that app/ is here, the autoloader can be built and package discovery run.
RUN composer dump-autoload --optimize --no-dev --no-interaction

# The .gitignore files keep these in the repo, but a stray build context might
# not carry them, and Laravel will not create them for itself.
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
        database \
    && chown -R www-data:www-data storage bootstrap/cache database

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
