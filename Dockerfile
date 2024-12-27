FROM php:8-fpm

# Install Nginx
RUN apt-get update && \
    apt-get install -y nginx && \
    rm -rf /var/lib/apt/lists/*

# Set ownership for web root
RUN chown -R www-data:www-data /var/www/html

# Set read and write permissions for web root
RUN chmod -R a+rw /var/www/html

# Create /var/lib/nginx/body directory and set ownership and permissions
RUN mkdir -p /var/lib/nginx/body && \
    chown -R www-data:www-data /var/lib/nginx/body && \
    chmod -R 755 /var/lib/nginx/body

# Configure Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
