#!/bin/bash

set -e

yum update -y
dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
dnf install -y https://dl.fedoraproject.org/pub/epel/epel-next-release-latest-9.noarch.rpm

dnf module install -y php:remi-8.2
yum install --skip-broken -y httpd memcached openssl wget zip unzip gcc \
                             java-1.8.0-openjdk libxml2 cabextract fontconfig mod_ssl vim procps

dnf install --skip-broken -y php php-cli php-common php-pear php-bcmath php-gd php-gmp php-imap php-intl     php-ldap     php-mbstring     php-odbc     php-pdo     php-pecl-apcu     php-pspell     php-zlib     php-snmp     php-soap     php-xml     php-xmlrpc     php-zts     php-devel     php-pecl-apcu-devel     php-pecl-memcache     php-calendar     php-shmop     php-intl     php-mcrypt     php-zip     php-pecl-zip php-pecl-uploadprogress
dnf install -y php-pecl-gearman
dnf install --skip-broken -y libgearman libgearman-devel php-sodium  git gearmand libgearman-dev libgearman-devel

yum install -y xorg-x11-fonts-75dpi

mkdir -p /run/php-fpm

yum -y install glibc-locale-source diffutils
localedef pt_BR -i pt_BR -f ISO-8859-1
localedef pt_BR.ISO-8859-1 -i pt_BR -f ISO-8859-1
localedef pt_BR.ISO8859-1 -i pt_BR -f ISO-8859-1

dnf install -y --nogpgcheck https://mirrors.rpmfusion.org/free/el/rpmfusion-free-release-$(rpm -E %rhel).noarch.rpm
dnf install -y https://mirrors.rpmfusion.org/nonfree/el/rpmfusion-nonfree-release-$(rpm -E %rhel).noarch.rpm
dnf config-manager --set-enabled crb
dnf install -y ffmpeg


cd /tmp/assets/pacotes

# fonts libraries
rpm -Uvh msttcore-fonts-2.0-3.noarch.rpm

# wkhtml
rpm -Uvh wkhtmltox-0.12.6.1-2.almalinux9.x86_64.rpm

mkdir /opt2
cd /opt && tar -cvzf remi.tgz remi
mv remi.tgz /opt2
cd -

if [ "$IMAGEM_APP_PACOTEMYSQL_PRESENTE" == "true" ]; then

  yum install -y php-mysql

fi

if [ "$IMAGEM_APP_PACOTESQLSERVER_PRESENTE" == "true" ]; then

    curl https://packages.microsoft.com/config/rhel/9/prod.repo | tee /etc/yum.repos.d/mssql-release.repo
    yum remove unixODBC-utf16 unixODBC-utf16-devel
    ACCEPT_EULA=Y yum install -y msodbcsql18
    ACCEPT_EULA=Y yum install -y mssql-tools18
    echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
    source ~/.bashrc
    yum install -y unixODBC-devel
    yum install -y php-sqlsrv-5.12.0

    # Ver issue #19
    mv /opt/microsoft /opt2
    ln -s /opt2/microsoft /opt/microsoft

fi

if [ "$IMAGEM_APP_PACOTEORACLE_PRESENTE" == "true" ]; then

    # ORACLE oci

    #yum install -y oracle-instantclient-basic-21.12.0.0.0-1.el9.x86_64.rpm
    #yum install -y oracle-instantclient-devel-21.12.0.0.0-1.el9.x86_64.rpm
    yum install -y oracle-instantclient-basic-23.6.0.24.10-1.el9.x86_64.rpm

    rm -rf oracle-instantclient-basic-23.6.0.24.10-1.el9.x86_64.rpm

    #yum install -y systemtap-sdt-devel
    #pecl channel-update pecl.php.net
    #export PHP_DTRACE=yes && echo "" | pecl install oci8-3.3.0 && unset PHP_DTRACE
    #echo "extension=oci8.so" > /etc/php.d/oci8.ini

    yum install -y php-oci8

fi

if [ "$IMAGEM_APP_PACOTEPOSTGRES_PRESENTE" == "true" ]; then

   yum install -y php-pgsql

fi

cd -

sed -i 's/;clear_env = no/clear_env = no/g' /etc/php-fpm.d/www.conf
