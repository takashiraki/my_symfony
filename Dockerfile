FROM php:8.4-apache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get update \
    && apt-get install -y unzip libpq-dev git vim sqlite3 libsqlite3-dev libicu-dev gh tmux lazygit symfony-cli default-jdk graphviz fonts-ipafont \
    && pecl install xdebug opentelemetry \
    && docker-php-ext-enable xdebug opentelemetry \
    && docker-php-ext-install mysqli pdo_mysql opcache intl \
    && composer global require laravel/installer \
    && (type -p wget >/dev/null || (apt-get update && apt-get install wget -y)) \
    && mkdir -p -m 755 /etc/apt/keyrings \
    && out=$(mktemp) && wget -nv -O$out https://cli.github.com/packages/githubcli-archive-keyring.gpg \
    && cat $out > /etc/apt/keyrings/githubcli-archive-keyring.gpg \
    && chmod go+r /etc/apt/keyrings/githubcli-archive-keyring.gpg \
    && mkdir -p -m 755 /etc/apt/sources.list.d \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" > /etc/apt/sources.list.d/github-cli.list \
    && a2enmod rewrite

ENV PATH="/root/.composer/vendor/bin:${PATH}"

COPY .docker/infra/php.ini /usr/local/etc/php/
COPY .docker/infra/000-default.conf /etc/apache2/sites-available/
COPY . /var/www/html/

COPY --from=node:22 /usr/local/bin /usr/local/bin
COPY --from=node:22 /usr/local/lib /usr/local/lib

WORKDIR /var/www/html