FROM php:5.6-apache
COPY src/ /usr/share/php/Ease
COPY debian/composer.json /usr/share/php/Ease/composer.json
COPY docs/  /usr/share/doc/ease-framework/html
