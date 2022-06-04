FROM php:8.0.2-fpm
RUN apt-get update
RUN apt-get install -y autoconf \
    pkg-config  \
    libssl-dev  \
    libzip-dev  \
    libpng-dev  \
    libonig-dev \
    git gcc make libc-dev vim unzip procps fish

RUN docker-php-ext-install bcmath pdo pdo_mysql mysqli sockets zip ctype iconv gd mbstring opcache
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/ \
&& ln -s /usr/local/bin/composer.phar /usr/local/bin/composer

## Install symfony cli
RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | tee /etc/apt/sources.list.d/symfony-cli.list
RUN apt-get update
RUN apt-get install -y symfony-cli
#RUN apt-get install -y symfony-cli php-symfony-polyfill-apcu

# Install npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x  | bash -
RUN apt-get -y install nodejs

WORKDIR /home/inserjeune

# Run symfony on localhot:8000
#ENTRYPOINT ["symfony", "server:start"]
