# Dockerfile (im Root-Verzeichnis)
FROM php:8.1-apache

# PHP Extensions installieren
RUN docker-php-ext-install pdo pdo_mysql

# Apache mod_rewrite aktivieren
RUN a2enmod rewrite

# Apache Konfiguration kopieren (Pfad korrigiert)
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Application kopieren
COPY . /var/www/html/

# Berechtigungen setzen
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ServerName für Apache setzen
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80