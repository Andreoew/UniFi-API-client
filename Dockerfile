FROM php:8.2-cli

RUN apt-get update && \
    apt-get install -y libcurl4-openssl-dev pkg-config git unzip && \
    docker-php-ext-install curl

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Garante que a dependência dotenv será instalada
RUN composer require vlucas/phpdotenv --no-interaction || true
RUN composer install --no-interaction

CMD ["php", "-S", "0.0.0.0:8443", "-t", "."] 