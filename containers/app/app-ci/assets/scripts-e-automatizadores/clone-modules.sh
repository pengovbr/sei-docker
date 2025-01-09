#!/bin/bash

mkdir -p /sei-modulos

cd /sei-modulos

git clone https://github.com/supergovbr/mod-sei-estatisticas.git
git clone https://github.com/supergovbr/mod-sei-pen.git
git clone https://github.com/supergovbr/mod-wssei.git
git clone https://github.com/anatelgovbr/mod-sei-peticionamento.git peticionamento
git clone https://github.com/supergovbr/mod-sei-protocolo-integrado.git

git clone https://${GITUSER_REPO_MODULOS}:${GITPASS_REPO_MODULOS}@github.com/pengovbr/mod-sei-resposta.git
cd mod-sei-resposta
git remote set-url origin https://github.com/pengovbr/mod-sei-resposta.git
cd ..

git clone https://${GITUSER_REPO_MODULOS}:${GITPASS_REPO_MODULOS}@github.com/pengovbr/mod-sei-incom.git
cd mod-sei-incom
git remote set-url origin https://github.com/pengovbr/mod-sei-incom.git
cd ..

git clone https://${GITUSER_REPO_MODULOS}:${GITPASS_REPO_MODULOS}@github.com/pengovbr/mod-gestao-documental.git
cd mod-gestao-documental
git remote set-url origin https://github.com/pengovbr/mod-gestao-documental.git
cd ..

git clone https://${GITUSER_REPO_MODULOS}:${GITPASS_REPO_MODULOS}@github.com/pengovbr/mod-sei-loginunico.git
cd mod-sei-loginunico
git remote set-url origin https://github.com/pengovbr/mod-sei-loginunico.git
cd ..

git clone https://${GITUSER_REPO_MODULOS}:${GITPASS_REPO_MODULOS}@github.com/pengovbr/mod-sei-assinatura-avancada.git
cd mod-sei-assinatura-avancada
git remote set-url origin https://github.com/pengovbr/mod-sei-assinatura-avancada.git
cd ..