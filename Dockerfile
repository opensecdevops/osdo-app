
FROM harbor.opensecdevops.com/osdo/osdo-octane-base@sha256:f70accbd22df77b0255dfba952de8d37e562c4791d112a1e8d14c14c4f58fe4d

COPY  --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

