
FROM harbor.opensecdevops.com/osdo/osdo-octane-base@sha256:0b92f3356970fda8b58a1fce2d51c9c4e724c526cf6ab359ac40b070cd37a3db

COPY  --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

