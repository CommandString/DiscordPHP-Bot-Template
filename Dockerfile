FROM composer/composer
FROM php:8.2-cli
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY . /bot
WORKDIR /bot
RUN composer install
CMD [ "php", "./Bot.php" ]
