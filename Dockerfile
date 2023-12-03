
FROM harbor.opensecdevops.com/osdo/osdo-octane-base@sha256:dc4c17f14a3adef7b296d78b3b5460e69831a253cee35de09e4360bf197cb6aa

COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/logs/ && \
    chown -R www-data:www-data /var/www/html 

WORKDIR /var/www/html

