FROM dunglas/frankenphp:php8.4

RUN install-php-extensions \
    intl \
    pdo_mysql \
    mbstring \
    opcache \
    zip \
    gd \
    redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
