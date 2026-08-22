#!/bin/sh
set -e

if [ "$APP_ENV" = "prod" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    php bin/console cache:clear --env=prod --no-debug
    php bin/console assets:install --env=prod
fi

exec docker-php-entrypoint apache2-foreground

COPY .docker/infra/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
ENTRYPOINT ["entrypoint.sh"]
