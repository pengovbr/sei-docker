#!/bin/bash

set -e

mkdir -p /tmp/assets/pacotes
cd /tmp/assets/pacotes

curl -L -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/msttcore-fonts-2.0-3.noarch.rpm \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/uploadprogress-2.0.2.tgz \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/wkhtmltox-0.12.6.1-2.almalinux9.x86_64.rpm \
    -O \
    https://github.com/pengovbr/sei-docker-binarios/raw/refs/heads/main/pacoteslinux/oracle-instantclient-basic-23.6.0.24.10-1.el9.x86_64.rpm

cd -