


#!/bin/bash

set -e

yum clean all
yum -y update

# Instalação dos componentes básicos do servidor web apache
yum -y install \
    httpd \
    memcached \
    openssl \
    wget \
    curl \
    zip \
    unzip \
    gcc \
    java-1.8.0-openjdk \
    libxml2 \
    cabextract \
    xorg-x11-font-utils \
    xorg-x11-fonts-75dpi \
    fontconfig \
    mod_ssl

# Instalação do PHP e demais extenções necessárias para o projeto
yum install -y epel-release yum-utils
yum install -y http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum-config-manager --enable remi-php73
yum -y update

# Instalação do PHP e demais extenções necessárias para o projeto
yum -y install \
    php php-common \
    php-cli \
    php-pear \
    php-bcmath \
    php-gd \
    php-gmp \
    php-imap \
    php-intl \
    php-ldap \
    php-mbstring \
    php-odbc \
    php-pdo \
    php-pecl-apcu \
    php-pspell \
    php-zlib \
    php-snmp \
    php-soap \
    php-xml \
    php-xmlrpc \
    php-zts \
    php-devel \
    php-pecl-apcu-devel \
    php-pecl-memcache \
    php-calendar \
    php-shmop \
    php-intl \
    php-mcrypt \
    php-zip \
    php-pecl-zip \
    gearmand \
    libgearman \
    libgearman-devel \
    php-pecl-gearman \
    vixie-cron \
    php-sodium \
    git \
    gearmand \
    libgearman-dev \
    libgearman-devel

cd /tmp/assets/
cp ca-certIN.pem /etc/pki/ca-trust/source/anchors/
update-ca-trust extract
update-ca-trust enable

cd /tmp/assets/pacotes

# Configuração do pacote de línguas pt_BR
localedef pt_BR -i pt_BR -f ISO-8859-1
localedef pt_BR.ISO-8859-1 -i pt_BR -f ISO-8859-1
localedef pt_BR.ISO8859-1 -i pt_BR -f ISO-8859-1

# Instalação do componentes UploadProgress
tar -zxvf uploadprogress.tgz
cd uploadprogress
phpize
./configure --enable-uploadprogress
make
make install
echo "extension=uploadprogress.so" > /etc/php.d/uploadprogress.ini
cd -

# wkhtml
rpm -Uvh wkhtmltox-0.12.6-1.centos7.x86_64.rpm

# fonts libraries
rpm -Uvh msttcore-fonts-2.0-3.noarch.rpm

if [ "$IMAGEM_APP_PACOTEMYSQL_PRESENTE" == "true" ]; then

  yum install -y mysql php-mysqli

fi

if [ "$IMAGEM_APP_PACOTESQLSERVER_PRESENTE" == "true" ]; then

    # Instalação dos componentes de conexão do SQL Server
    curl https://packages.microsoft.com/config/rhel/7/prod.repo > /etc/yum.repos.d/mssql-release.repo
    ACCEPT_EULA=Y yum install -y msodbcsql17
    yum install -y libtool-ltdl-devel libodbc1 unixODBC unixODBC-devel php-mssql php-pdo
    pecl channel-update pecl.php.net
    pecl install sqlsrv-5.10.1 pdo_sqlsrv-5.10.1
    printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php.d/20-sqlsrv.ini
    printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php.d/30-pdo_sqlsrv.ini

    # Ver issue #19
    mkdir /opt2
    mv /opt/microsoft /opt2
    ln -s /opt2/microsoft /opt/microsoft

fi

if [ "$IMAGEM_APP_PACOTEORACLE_PRESENTE" == "true" ]; then

    # ORACLE oci

    yum install -y \
        oracle-instantclient12.2-basic-12.2.0.1.0-1.x86_64.rpm \
        oracle-instantclient12.2-devel-12.2.0.1.0-1.x86_64.rpm \
        oracle-instantclient12.2-sqlplus-12.2.0.1.0-1.x86_64.rpm

    echo /usr/lib/oracle/12.2/client64/lib > /etc/ld.so.conf.d/oracle-instantclient.conf
    ldconfig

    # Install Oracle extensions
    yum install -y php-dev php-pear build-essential systemtap-sdt-devel
    pecl channel-update pecl.php.net
    export PHP_DTRACE=yes && pecl install oci8-2.2.0 && unset PHP_DTRACE

    echo "extension=oci8.so" > /etc/php.d/oci8.ini

    mkdir /orapdo
    cd /orapdo
    mv /tmp/assets/pacotes/php-7.3.33.tar.gz .
    tar -xvzf php-7.3.33.tar.gz
    rm -rf php-7.3.33.tar.gz
    cd php-7.3.33
    cd ext/pdo_oci
    phpize
    ./configure --with-pdo-oci=instantclient,/usr/lib/oracle/12.2/client64/lib,12.2
    make
    make install
    echo extension=pdo_oci.so > /etc/php.d/pdo_oci.ini
    cd /tmp/assets/pacotes

fi

if [ "$IMAGEM_APP_PACOTEPOSTGRES_PRESENTE" == "true" ]; then

   yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm
   yum install -y postgresql15 postgresql15-libs php-pgsql php-pecl-pq

fi

cd -
