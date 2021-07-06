# Modified from https://github.com/jackbrycesmith/laravel-caprover-template
ARG PHP_VERSION=${PHP_VERSION:-7.3}

# TODO check why caddy and composer is not automatically pulled
# TODO chown root is needed for BITBUCKET ISSUE (pipeline)
FROM caddy:2.2.1 AS caddy
RUN chown root:root -R /usr/bin/caddy

FROM php:${PHP_VERSION}-fpm-alpine3.13 AS php-system-setup

# Install system dependencies
RUN apk add --no-cache dcron busybox-suid libcap curl zip unzip git npm libc6-compat
RUN ln -s /lib/libc.musl-x86_64.so.1 /lib/ld-linux-x86-64.so.2

# Install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions intl bcmath gd pdo_mysql pdo_pgsql opcache redis uuid exif pcntl zip

# Install supervisord implementation
COPY --from=ochinchina/supervisord:latest /usr/local/bin/supervisord /usr/local/bin/supervisord

# Install composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY --from=caddy /usr/bin/caddy /usr/local/bin/caddy
RUN setcap 'cap_net_bind_service=+ep' /usr/local/bin/caddy

FROM php-system-setup AS app-setup

# Set working directory
ENV LARAVEL_PATH=/srv/app
WORKDIR $LARAVEL_PATH

# Add non-root user: 'app'
ARG NON_ROOT_GROUP=${NON_ROOT_GROUP:-app}
ARG NON_ROOT_USER=${NON_ROOT_USER:-app}
RUN addgroup -S $NON_ROOT_GROUP && adduser -S $NON_ROOT_USER -G $NON_ROOT_GROUP
RUN addgroup $NON_ROOT_USER wheel

# install newrelic
RUN \
  curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-9.17.1.301-linux.tar.gz | tar -C /tmp -zx && \
  export NR_INSTALL_USE_CP_NOT_LN=1 && \
  export NR_INSTALL_SILENT=1 && \
  /tmp/newrelic-php5-*/newrelic-install install && \
  rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
  sed -i \
      -e 's/"REPLACE_WITH_REAL_KEY"/"eu01xx20edcd8e0ef98989f8bbd7a36ff132NRAL"/' \
      -e 's/newrelic.appname = "PHP Application"/newrelic.appname = "qlinik-api-development"/' \
      -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
      -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
      /usr/local/etc/php/conf.d/newrelic.ini
RUN chmod -R 777 /var/log/newrelic

# Set cron job
COPY ./.deploy/config/crontab /etc/crontabs/$NON_ROOT_USER
RUN chmod 777 /usr/sbin/crond
RUN chown -R $NON_ROOT_USER:$NON_ROOT_GROUP /etc/crontabs/$NON_ROOT_USER && setcap cap_setgid=ep /usr/sbin/crond

# BITBUCKET ISSUE
# switch caddy to non root app
RUN chown -R $NON_ROOT_USER:$NON_ROOT_GROUP /usr/local/bin/caddy


# Switch to non-root 'app' user & install app dependencies
COPY composer.json composer.lock ./
RUN chown -R $NON_ROOT_USER:$NON_ROOT_GROUP $LARAVEL_PATH
USER $NON_ROOT_USER

RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader
RUN rm -rf /home/$NON_ROOT_USER/.composer

# Copy app
COPY --chown=$NON_ROOT_USER:$NON_ROOT_GROUP . $LARAVEL_PATH/
COPY ./.deploy/config/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Set any ENVs
ARG APP_KEY=${APP_KEY}
ARG APP_NAME=${APP_NAME}
ARG APP_URL=${APP_URL}
ARG APP_ENV=${APP_ENV}
ARG APP_DEBUG=${APP_DEBUG}

ARG LOG_CHANNEL=${LOG_CHANNEL}

ARG DB_CONNECTION=${DB_CONNECTION}
ARG DB_HOST=${DB_HOST}
ARG DB_PORT=${DB_PORT}
ARG DB_DATABASE=${DB_DATABASE}
ARG DB_USERNAME=${DB_USERNAME}
ARG DB_PASSWORD=${DB_PASSWORD}

ARG BROADCAST_DRIVER=${BROADCAST_DRIVER}
ARG CACHE_DRIVER=${CACHE_DRIVER}
ARG QUEUE_CONNECTION=${QUEUE_CONNECTION}
ARG SESSION_DRIVER=${SESSION_DRIVER}
ARG SESSION_LIFETIME=${SESSION_LIFETIME}

ARG REDIS_HOST=${REDIS_HOST}
ARG REDIS_PASSWORD=${REDIS_PASSWORD}
ARG REDIS_PORT=${REDIS_PORT}

ARG MAIL_MAILER=${MAIL_MAILER}
ARG MAIL_HOST=${MAIL_HOST}
ARG MAIL_PORT=${MAIL_PORT}
ARG MAIL_USERNAME=${MAIL_USERNAME}
ARG MAIL_PASSWORD=${MAIL_PASSWORD}
ARG MAIL_ENCRYPTION=${MAIL_ENCRYPTION}
ARG MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
ARG MAIL_ENCRYPTION=${MAIL_ENCRYPTION}
ARG MAIL_FROM_NAME=${APP_NAME}

ARG PUSHER_APP_ID=${PUSHER_APP_ID}
ARG PUSHER_APP_KEY=${PUSHER_APP_KEY}
ARG PUSHER_APP_SECRET=${PUSHER_APP_SECRET}
ARG PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}

# Start app
EXPOSE 80
COPY ./.deploy/entrypoint.sh /

ENTRYPOINT ["sh", "/entrypoint.sh"]
