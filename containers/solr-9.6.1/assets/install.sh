#!/bin/bash

set -e

yum -y update && yum -y install lsof wget glibc-locale-source procps bc

localedef pt_BR -i pt_BR -f ISO-8859-1

dnf -y install java-17-openjdk

# Instalação de pacote de fontes do windows
rpm -Uvh /tmp/msttcore-fonts-2.0-3.noarch.rpm

# join splited solr
# cat /tmp/solr-9.6.1.tgz.part* > /tmp/solr-9.6.1.tgz

mv /tmp/solr9.6.1sei/* /tmp/

useradd solr

chmod +x /tmp/sei-solr-9.6.1.sh

cd /tmp

/tmp/sei-solr-9.6.1.sh

echo "" >> /opt/solr/bin/solr.in.sh
echo 'SOLR_OPTS="$SOLR_OPTS -Dsolr.allowPaths=/dados"' >> /opt/solr/bin/solr.in.sh
echo 'SOLR_JETTY_HOST="0.0.0.0"' >> /opt/solr/bin/solr.in.sh

mv /tmp/command.sh /

yum clean all
rm -rf /var/cache/yum

# Configuração de permissões de execução no script de inicialização do container
chmod +x /command.sh
