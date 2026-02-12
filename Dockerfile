FROM php:8.2-cli

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install bcmath

WORKDIR /var/www/html

COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
