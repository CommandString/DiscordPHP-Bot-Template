FROM ubuntu:23.04

ARG DEBIAN_FRONTEND=noninteractive

COPY . /usr/src/bot
RUN rm -rf .github
RUN rm -rf .git

WORKDIR /usr/src/bot


RUN apt-get update
RUN apt-get install php-cli php-xml composer php-bcmath -y

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer install

CMD [ "php", "./Bot.php" ]