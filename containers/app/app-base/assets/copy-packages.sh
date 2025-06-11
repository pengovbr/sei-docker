#!/bin/bash

set -e

mkdir -p /tmp/assets/pacotes
cd /tmp/assets/pacotes

curl -L -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/msttcore-fonts-2.0-3.noarch.rpm \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/oracle-instantclient12.2-basic-12.2.0.1.0-1.x86_64.rpm \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/oracle-instantclient12.2-devel-12.2.0.1.0-1.x86_64.rpm \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/oracle-instantclient12.2-sqlplus-12.2.0.1.0-1.x86_64.rpm \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/php-7.3.33.tar.gz \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/uploadprogress.tgz \
    -O \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/wkhtmltox-0.12.6-1.centos7.x86_64.rpm 

cd -