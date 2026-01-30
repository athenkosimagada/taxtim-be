FROM php:8.3-cli

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files
COPY . /usr/src/app

# Set working directory
WORKDIR /usr/src/app

# Expose API port
EXPOSE 8000

# IMPORTANT: route ALL requests through index.php
CMD ["php", "-S", "0.0.0.0:8000", "index.php"]
