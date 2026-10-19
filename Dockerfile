FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installing packages above can trigger Debian's apache2 postinst to re-enable
# mpm_event alongside the mpm_prefork this image already uses for mod_php,
# which makes Apache refuse to start ("More than one MPM loaded"). Force prefork.
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork rewrite

# Web root is public/ — app/, config/, database/ are never reachable because they
# sit outside it entirely (same as the "real server" setup described in README.md).
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker-apache.conf /etc/apache2/conf-enabled/zz-greenoffice.conf

COPY . /var/www/html/
RUN rm -f /var/www/html/config/config.php

RUN mkdir -p /var/www/html/public/uploads/docs /var/www/html/public/uploads/news \
    && chown -R www-data:www-data /var/www/html/public/uploads

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
