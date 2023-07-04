FROM php:8.2.7-apache




# Install necessary Apache packages

RUN apt-get update && apt-get install -y \

libicu-dev \

libzip-dev \

&& docker-php-ext-install intl pdo_mysql zip \

&& a2enmod rewrite




# Set up the Apache configuration

COPY ./apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Install Composer

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer




# Copy the application files

COPY . /var/www/html




# Install dependencies

WORKDIR /var/www/html

RUN a2enmod rewrite


# Set COMPOSER_ALLOW_SUPERUSER environment variable
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN rm composer.lock

# Install dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Expose port 80

EXPOSE 80




# Start Apache

CMD ["apache2-foreground"]