#!/bin/bash

set -e

mv /tmp/assets/scripts-e-automatizadores/command.sh \
   /tmp/assets/scripts-e-automatizadores/ConfiguracaoSip.php \
   /tmp/assets/scripts-e-automatizadores/ConfiguracaoSEI.php \
   /

chmod +x /command.sh

yum -y update

#yum install -y http://li.nux.ro/download/nux/dextop/el7/x86_64/nux-dextop-release-0-5.el7.nux.noarch.rpm

# Instalação de ferramentas utilitárias e dependências do SEI
yum -y groupinstall 'Development Tools'
yum install -y cronie mysql net-tools nc


# Instalação do XDebug, versão 3
pecl install xdebug-3.3.2

# Configuração de permissão do diretório de arquivos
mkdir -p /var/sei/arquivos
chmod -R 777 /var/sei/arquivos

# Configuração dos serviços de background do Cron
mkdir /var/log/sei

cp /tmp/assets/conf/info.php /var/www/html/
cp /tmp/assets/conf/sei.ini /etc/php.d/
cp /tmp/assets/conf/sei.conf /etc/httpd/conf.d/
cp /tmp/assets/conf/deflate.conf /etc/httpd/conf.d/
cp /tmp/assets/conf/cron.conf /etc/cron.d/sei
chown -R root:root /etc/cron.d/
chmod 0644 /etc/cron.d/sei
cp /tmp/assets/conf/xdebug.ini /etc/php.d/

sed -i "s|^SSLCertificateFile |#SSLCertificateFile|" /etc/httpd/conf.d/ssl.conf
sed -i "s|^SSLCertificateKeyFile |#SSLCertificateKeyFile|" /etc/httpd/conf.d/ssl.conf
sed -i "s|^SSLEngine on|SSLEngine off|" /etc/httpd/conf.d/ssl.conf

# Remover arquivos temporários
yum clean all
rm -rf /var/cache/yum