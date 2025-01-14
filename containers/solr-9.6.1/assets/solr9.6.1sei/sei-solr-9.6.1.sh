#!/bin/sh

VERSAOSOLR=9.6.1

clear

for n in /tmp/solr-${VERSAOSOLR}.tgz /tmp/log4j.properties /tmp/solr.service
 do
  if [ ! -f $n ]; then
      echo "Arquivo ["$n"] nao encontrado."
      exit 1
  fi
done

if [ ! -d "/tmp/sei-cores-${VERSAOSOLR}" ]; then
  echo "Diretorio [/tmp/sei-cores-${VERSAOSOLR}] nao encontrado."
  exit 1
fi

cd /tmp

tar -xf /tmp/solr-${VERSAOSOLR}.tgz

cp -Rf /tmp/solr-${VERSAOSOLR} /opt/solr

mkdir -v /dados

ln -vs /dados /opt/solr/server/solr

cp -Rfv /tmp/log4j.properties /opt/solr/server/resources

cp -Rf /tmp/sei-cores-${VERSAOSOLR}/* /dados

mv /tmp/solr-${VERSAOSOLR}/server/solr/configsets/sample_techproducts_configs/conf/solrconfig.xml /opt/solr/solrconfig.bak

cp -R /tmp/solr-${VERSAOSOLR}/server/solr/configsets/sample_techproducts_configs/conf /dados/sei-protocolos
cp -R /tmp/solr-${VERSAOSOLR}/server/solr/configsets/sample_techproducts_configs/conf /dados/sei-bases-conhecimento
cp -R /tmp/solr-${VERSAOSOLR}/server/solr/configsets/sample_techproducts_configs/conf /dados/sei-publicacoes

rm -Rf /opt/solr/example

mkdir -v /dados/sei-protocolos/conteudo
mkdir -v /dados/sei-bases-conhecimento/conteudo
mkdir -v /dados/sei-publicacoes/conteudo

cp -f /tmp/security.json /opt/solr/server/solr/security.json

chown -R solr.solr /dados
chown -R solr.solr /opt/solr/

cp solr.service /etc/systemd/system/solr.service

