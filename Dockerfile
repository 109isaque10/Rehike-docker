FROM nginx:alpine
ADD nginx/default.conf /etc/nginx/conf.d

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
