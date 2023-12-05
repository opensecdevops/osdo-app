
FROM harbor.opensecdevops.com/osdo/osdo-octane-base@sha256:e27ebab41201b59321b1d789018cb1869574d4bb69001b184e3d0d7bf3e38b87

COPY  --chown=octane:octane . /var/www/html

WORKDIR /var/www/html

