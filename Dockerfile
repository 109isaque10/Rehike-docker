FROM php:8-fpm

# Install Nginx
RUN apt-get update && \
    apt-get install -y nginx && \
    rm -rf /var/lib/apt/lists/*

# Copy application files
COPY . /var/www/html

# Configure Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD service php8.0-fpm start && nginx -g "daemon off;"