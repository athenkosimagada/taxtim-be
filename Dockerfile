FROM php:8.3-cli-alpine

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Create app directory
WORKDIR /app

# Copy only what we need
COPY . .

# Expose port Railway expects
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000", "index.php"]