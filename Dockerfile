# GlobeTrek — single-container image.
#
# The app is SQLite-only and serves its own static assets, so there is no
# database service, no nginx and no node build to orchestrate: PHP is the whole
# runtime. `php artisan serve` is the entrypoint, which is right for a demo and
# for local development; put a real web server in front of it before production.

FROM php:8.4-cli

# unzip/git let Composer install from dist archives; libsqlite3-dev is what
# pdo_sqlite compiles against; libzip-dev backs the zip extension Composer uses.
# gd is not needed to serve the site — uploads are sized with core getimagesize()
# — but the test suite fakes uploads via UploadedFile::fake()->image(), which
# calls imagecreatetruecolor(). Without it one test fails inside the container.
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      git \
      unzip \
      libzip-dev \
      libsqlite3-dev \
      libpng-dev \
      libjpeg62-turbo-dev \
      libwebp-dev \
      libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install -j"$(nproc)" pdo_sqlite zip gd \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /app

# Dependencies first, so editing application code does not invalidate the
# Composer layer. Dev dependencies are kept: the image can run the test suite.
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

# The database and .env live under /app/storage, which docker-compose mounts as
# a named volume — that is what makes data and the app key survive a restart.
# The path is deliberately NOT set as a container ENV var: phpunit.xml pins
# DB_DATABASE to :memory:, and PHPUnit will not override a variable that already
# exists in the environment, so an ENV here would point the test suite at the
# real database and RefreshDatabase would wipe it. The entrypoint writes the
# path into .env instead, which artisan and requests both read.

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
  CMD php -r 'exit(@file_get_contents("http://127.0.0.1:8000/") === false ? 1 : 0);'

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
