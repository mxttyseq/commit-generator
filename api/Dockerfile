# Soubor: api/Dockerfile
FROM php:8.5-apache

# 1. Instalace systémových závislostí (přesunuto nahoru pro cachování)
RUN apt-get update \
 && apt-get install -y curl zstd \
 && rm -rf /var/lib/apt/lists/*

# 2. Instalace PHP rozšíření
RUN docker-php-ext-install pdo pdo_mysql

# 3. Nastavení Apache konfigurace (Port 8081 a DocumentRoot)
RUN sed -i 's/Listen 80/Listen 8081/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:8081/g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/www|g' /etc/apache2/sites-available/000-default.conf

# 4. Povolení mod_rewrite a úprava AllowOverride
RUN a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 5. Zkopírování projektu
# Pozor: Kopírujeme do kontejneru. Context bude nastaven v docker-compose.
COPY . /var/www/html/

# 6. Práva
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Pokud složky neexistují v repozitáři, vytvoříme je, aby chmod neselhal
RUN mkdir -p /var/www/html/temp /var/www/html/log \
    && chmod -R 777 /var/www/html/temp \
    && chmod -R 777 /var/www/html/log

EXPOSE 8081

CMD ["apache2-foreground"]