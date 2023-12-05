
FROM harbor.opensecdevops.com/osdo/osdo-octane-base@sha256:ce6c221c1efb9f6ba507aec0f220323c7194936abe452bf6113b8ea9a356825b

COPY  --chown=octane:octane . /var/www/html

WORKDIR /var/www/html

