FROM composer/composer
FROM php:8.2-cli

RUN apt update && apt install -y git

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /bot
WORKDIR /bot
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install
CMD [ "php", "./Bot.php" ]