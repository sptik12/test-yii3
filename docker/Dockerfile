FROM yiisoftware/yii-php:8.2-apache

ARG PHP_ENV=Development

RUN set -ex; \
	apt-get update; \
	apt-get install -y --no-install-recommends default-mysql-client ca-certificates git unzip mc ffmeg; \

	if [ "$PHP_ENV" = "Production" ]; then \
	  ini_file="$PHP_INI_DIR/php.ini-production"; \
	else \
	  ini_file="$PHP_INI_DIR/php.ini-development"; \
	fi; \
	cp $ini_file "$PHP_INI_DIR/php.ini"

# Copy custom PHP ini
COPY ./etc/php/conf.d/base.ini /usr/local/etc/php/conf.d/base.ini

# Copy apache files
COPY ./etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./etc/apache2/sites-available/010-dealer.conf  /etc/apache2/sites-available/010-dealer.conf
COPY ./etc/apache2/sites-available/020-admin.conf   /etc/apache2/sites-available/020-admin.conf
	
RUN set -ex; \
	a2ensite 000-default.conf 010-dealer.conf 020-admin.conf
	
COPY ./docker-run.sh /
RUN chmod +x /docker-run.sh

CMD /docker-run.sh
