FROM nginx:alpine
ADD default.conf /etc/nginx/conf.d
CMD ["chmod", "-R", "777", "/var/www/html"]