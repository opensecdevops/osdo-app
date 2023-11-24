#!/usr/bin/expect

set timeout -1
set password ${COSIGN_PASSWORD}

spawn cosign sign --key .secure_files/cosign.key -y $DOCKER_REGISTRY_URL/osdo/osdo-app@${DIGEST}
expect "Enter password for private key:"
send "$password\r"
expect eof
