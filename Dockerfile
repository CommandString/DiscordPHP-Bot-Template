FROM composer/composer as COMPOSER

COPY . /bot
WORKDIR /bot
RUN composer install

FROM php:8.2-cli

COPY --from=COMPOSER /bot /bot
WORKDIR /bot

CMD [ "php", "./Bot.php" ]
