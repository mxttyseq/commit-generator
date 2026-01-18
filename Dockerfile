FROM php:8.5-apache

# Nainstaluj PHP rozšíření
RUN docker-php-ext-install pdo pdo_mysql

# Zkopíruj projekt
COPY . /var/www/html/

# DŮLEŽITÉ: Nastav správné práva
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/temp \
    && chmod -R 777 /var/www/html/log

# Změň DocumentRoot na /var/www/html/www
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/www|g' /etc/apache2/sites-available/000-default.conf

# PŘIDEJ Directory block pro www/
RUN echo '<Directory /var/www/html/www/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Změň port na 8081
RUN sed -i 's/Listen 80/Listen 8081/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:8081/g' /etc/apache2/sites-available/000-default.conf

# Zapni mod_rewrite
RUN a2enmod rewrite

# Povol .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

EXPOSE 8081

CMD ["apache2-foreground"]

RUN apt-get update \
 && apt-get install -y curl zstd \
 && rm -rf /var/lib/apt/lists/*
