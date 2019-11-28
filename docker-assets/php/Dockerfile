FROM php:7.3-apache

ARG WEB_USER
ARG WEB_GROUP
ARG UID

# Installing all required packages.
RUN apt-get update -y && apt-get install -y git && apt-get install -y zip && apt-get install -y unzip

# Configuring Apache.
ENV APACHE_PATH=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_PATH}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_PATH}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite headers

# Configuring php.ini
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN pecl install -f xdebug 
RUN docker-php-ext-enable xdebug

# Coniguring composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=. --filename=composer
RUN mv composer /usr/local/bin/

# Configuring User

RUN useradd -G $WEB_GROUP,root -u $UID -d /home/$WEB_USER $WEB_USER
RUN mkdir -p /home/$WEB_USER/.composer && \
    chown -R $WEB_USER:$WEB_USER /home/$WEB_USER

RUN chown -R $WEB_GROUP:$WEB_GROUP /var/www/html/