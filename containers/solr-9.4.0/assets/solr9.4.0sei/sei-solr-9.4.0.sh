#!/bin/sh


for n in /tmp/solr-9.4.0.tgz /tmp/log4j.properties /tmp/solr.service
 do
  if [ ! -f $n ]; then
      echo "Arquivo ["$n"] nao encontrado."
      exit 1
  fi
done

if [ ! -d "/tmp/sei-cores-9.4.0" ]; then
  echo "Diretorio [/tmp/sei-cores-9.4.0] nao encontrado."
  exit 1
fi

cd /tmp

tar -xf /tmp/solr-9.4.0.tgz

cp -Rf /tmp/solr-9.4.0 /opt/solr

mkdir -v /dados

ln -vs /dados /opt/solr/server/solr

cp -Rfv /tmp/log4j.properties /opt/solr/server/resources

cp -Rf /tmp/sei-cores-9.4.0/* /dados

mv /tmp/solr-9.4.0/server/solr/configsets/sample_techproducts_configs/conf/solrconfig.xml /opt/solr/solrconfig.bak

cp -R /tmp/solr-9.4.0/server/solr/configsets/sample_techproducts_configs/conf /dados/sei-protocolos
cp -R /tmp/solr-9.4.0/server/solr/configsets/sample_techproducts_configs/conf /dados/sei-bases-conhecimento
cp -R /tmp/solr-9.4.0/server/solr/configsets/sample_techproducts_configs/conf /dados/sei-publicacoes

rm -Rf /opt/solr/example

mkdir -v /dados/sei-protocolos/conteudo
mkdir -v /dados/sei-bases-conhecimento/conteudo
mkdir -v /dados/sei-publicacoes/conteudo

cp -f /tmp/security.json /opt/solr/server/solr/security.json

chown -R solr.solr /dados
chown -R solr.solr /opt/solr/

cp solr.service /etc/systemd/system/solr.service

