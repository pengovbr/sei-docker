#!/usr/bin/env sh

# Configuração do pacote de línguas pt_BR
localedef pt_BR -i pt_BR -f ISO-8859-1

# Corrige os repositórios CentOS para resolver o erro "Could not resolve host: mirrorlist.centos.org; Unknown error"
# Conforme solução oficial https://access.redhat.com/solutions/7077708
sed -i s/mirror.centos.org/vault.centos.org/g /etc/yum.repos.d/CentOS-*.repo
sed -i s/^#.*baseurl=http/baseurl=http/g /etc/yum.repos.d/CentOS-*.repo
sed -i s/^mirrorlist=http/#mirrorlist=http/g /etc/yum.repos.d/CentOS-*.repo

# Instalação do utilitários necessários para o provisionamento
yum -y install unzip java-1.7.0-openjdk wget unzip libreoffice libreoffice-headless

# Instalação da api de serviços de conversão de documentos
unzip /jodconverter-tomcat-2.2.2.zip -d /opt
rm -rf /jodconverter-tomcat-2.2.2.zip

# Remover arquivos temporários
yum clean all
rm -rf /var/cache/yum

# Configuração de permissões de execução no script de inicialização do container
chmod +x /entrypoint.sh

exit 0
