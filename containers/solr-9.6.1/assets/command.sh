#!/usr/bin/env sh

if [ -z "$( ls -A '/dados' )" ]; then
      echo "O diretorio /dados está vazio. Vamos inicializa-lo copiando o conteúdo disponível em /dados-modelo"
      cp -R /dados-modelo/* /dados/
else
      echo "O diretorio /dados já possui conteúdo. Não é necessário inicializa-lo"
fi

/opt/solr/bin/solr -p 8983 -f
