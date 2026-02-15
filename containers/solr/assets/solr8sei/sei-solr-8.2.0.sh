#!/bin/sh

for n in /tmp/solr-8.2.0.tgz /tmp/log4j.properties /tmp/solr.service
 do
  if [ ! -f $n ]; then
      echo "Arquivo ["$n"] nao encontrado."
      exit 1
  fi
done

if [ ! -d "/tmp/sei-cores-8.2.0" ]; then
  echo "Diretorio [/tmp/sei-cores-8.2.0] nao encontrado."
  exit 1
fi

cd /tmp

tar -xf /tmp/solr-8.2.0.tgz

cp -Rf /tmp/solr-8.2.0 /opt/solr

mkdir -v /dados

ln -vs /dados /opt/solr/server/solr

cp -Rfv /tmp/log4j.properties /opt/solr/server/resources

cp -Rf /tmp/sei-cores-8.2.0/* /dados

mv /opt/solr/example/files/conf/solrconfig.xml /opt/solr/example/files/conf/solrconfig.bak

cp -R /opt/solr/example/files/conf /dados/sei-protocolos
cp -R /opt/solr/example/files/conf /dados/sei-bases-conhecimento
cp -R /opt/solr/example/files/conf /dados/sei-publicacoes

rm -Rf /opt/solr/example

mkdir -v /dados/sei-protocolos/conteudo
mkdir -v /dados/sei-bases-conhecimento/conteudo
mkdir -v /dados/sei-publicacoes/conteudo

# Copia o conteudo modelo para o diretório /dados-modelo
# Util para inicializar o diretório /dados quando mapeando um volume vazio em /dados
cp -Rfv /dados /dados-modelo

chown -R solr.solr /dados
chown -R solr.solr /dados-modelo
chown -R solr.solr /opt/solr/

cp solr.service /etc/systemd/system/solr.service

