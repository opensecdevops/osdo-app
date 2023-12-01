
FROM harbor.opensecdevops.com/osdo/osdo-octane-base@sha256:4f1d75cecaab72ac968089f68bba41ffa853ddfb41e30ac40bc0c8e83144fd78

COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/logs/ && \
    chown -R www-data:www-data /var/www/html 

WORKDIR /var/www/html

