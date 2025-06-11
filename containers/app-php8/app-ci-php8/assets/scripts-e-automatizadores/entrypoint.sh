#!/bin/bash

# ENVIRONMENT VARIABLES
# $APP_PROTOCOLO
# $APP_HOST
# $APP_ORGAO
# $APP_ORGAO_DESCRICAO
# $APP_NOMECOMPLEMENTO
# $APP_DB_TIPO
# $APP_DB_PORTA
# $APP_DB_SIP_USERNAME
# $APP_DB_SIP_PASSWORD
# $APP_DB_SEI_USERNAME
# $APP_DB_SEI_PASSWORD
# $APP_DB_ROOT_USERNAME
# $APP_DB_ROOT_PASSWORD

set -e

if [ -z "$APP_PROTOCOLO" ] || \
   [ -z "$APP_HOST" ] || \
   [ -z "$APP_ORGAO" ] || \
   [ -z "$APP_ORGAO_DESCRICAO" ] || \
   [ -z "$APP_NOMECOMPLEMENTO" ] || \
   [ -z "$APP_DB_TIPO" ] || \
   [ -z "$APP_DB_PORTA" ] || \
   [ -z "$APP_DB_SEI_BASE" ] || \
   [ -z "$APP_DB_SIP_BASE" ] || \
   [ -z "$APP_DB_SIP_USERNAME" ] || \
   [ -z "$APP_DB_SIP_PASSWORD" ] || \
   [ -z "$APP_DB_SEI_USERNAME" ] || \
   [ -z "$APP_DB_SEI_PASSWORD" ] || \
   [ -z "$APP_DB_ROOT_USERNAME" ] || \
   [ -z "$APP_DB_ROOT_PASSWORD" ]; then
    echo "Informe as seguinte variaveis de ambiente no seu docker-compose ou no container:"
    echo "APP_PROTOCOLO=$APP_PROTOCOLO"
    echo "APP_HOST=$APP_HOST"
    echo "APP_ORGAO=$APP_ORGAO"
    echo "APP_ORGAO_DESCRICAO=$APP_ORGAO_DESCRICAO"
    echo "APP_NOMECOMPLEMENTO=$APP_NOMECOMPLEMENTO"
    echo "APP_DB_TIPO=$APP_DB_TIPO (deve ser MySql, Sqlserver ou Oracle)"
    echo "APP_DB_PORTA=$APP_DB_PORTA"
    echo "APP_DB_SIP_BASE=$APP_DB_SIP_BASE"
    echo "APP_DB_SEI_BASE=$APP_DB_SEI_BASE"
    echo "APP_DB_SIP_USERNAME=$APP_DB_SIP_USERNAME"
    echo "APP_DB_SIP_PASSWORD=$APP_DB_SIP_PASSWORD"
    echo "APP_DB_SEI_USERNAME=$APP_DB_SEI_USERNAME"
    echo "APP_DB_SEI_PASSWORD=$APP_DB_SEI_PASSWORD"
    echo "APP_DB_ROOT_USERNAME=$APP_DB_ROOT_USERNAME"
    echo "APP_DB_ROOT_PASSWORD=$APP_DB_ROOT_PASSWORD"

    exit 1
fi

echo "***************************************************"
echo "***************************************************"
echo "**INICIANDO CONFIGURACOES BASICAS DO APACHE E SEI**"
echo "***************************************************"
echo "***************************************************"

while [ ! -f /sei/controlador-instalacoes/instalado.ok ]
do
    echo "Aguardando conteiner atualizador instalar e atualizar o SEI e modulos"
    sleep 5
done

APP_HOST_URL=$APP_PROTOCOLO://$APP_HOST

echo "127.0.0.1 $APP_HOST" >> /etc/hosts

# Direciona logs para saida padrao para utilizar docker logs
ln -sf /dev/stdout /var/log/httpd/access_log
ln -sf /dev/stdout /var/log/httpd/ssl_access_log
ln -sf /dev/stdout /var/log/httpd/ssl_request_log
ln -sf /dev/stderr /var/log/httpd/error_log
ln -sf /dev/stderr /var/log/httpd/ssl_error_log

# vefificar se existe codigo fonte
if [ ! -f /opt/sei/web/SEI.php ] || [ ! -f /opt/sip/web/Sip.php ] ; then
  echo "Codigo fonte do sei  ou sip nao encontrado ou sem permissao. Abandonando subida..."
  sleep 10
  exit 1
fi

# verificar se config esta correto

if [ ! -f /opt/sei/config/ConfiguracaoSEI.php ]; then
    \cp -r /sei/files/conf/ConfiguracaoSEI.php /opt/sei/config/
fi

if [ ! -f /opt/sip/config/ConfiguracaoSip.php ]; then
    \cp -r /sei/files/conf/ConfiguracaoSip.php /opt/sip/config/
fi

cp -f /opt/sei/config/ConfiguracaoSEI.php /opt/sei/config/ConfiguracaoSEI.php.$(date '+%Y-%m-%d_%H-%M-%S')
cp -f /opt/sip/config/ConfiguracaoSip.php /opt/sip/config/ConfiguracaoSip.php.$(date '+%Y-%m-%d_%H-%M-%S')


# Ajustes de permissoes diversos para desenvolvimento do SEI
chmod +x /opt/sei/bin/pdfboxmerge.jar
mkdir -p /opt/sei/temp
mkdir -p /opt/sip/temp
chmod -R 777 /opt/sei/temp
chmod -R 777 /opt/sip/temp



while [ ! -f /sei/controlador-instalacoes/instalado.ok ]
do
    echo "Aguardando conteiner atualizador instalar e atualizar o SEI e modulos"
    sleep 5
done

# Ver issue #19
if [ "$DATABASE_TYPE" = "SqlServer" ]; then
    ln -s /opt2/microsoft /opt/microsoft
fi

\cp /opt2/remi.tgz /opt/
cd /opt && tar -xvzf remi.tgz
rm -rf remi.tgz
cd -

if [ "$APP_DB_TIPO" = "SqlServer" ] && [ "$APP_DB_HOST" = "db" ]; then

    set +e
    grep "'Encrypt' => false" /opt/infra/infra_php/InfraSqlServer.php
    e=$?
    set -e

    if [ ! "$e" = "0" ]; then
        sed -i "s|MultipleActiveResultSets' => false,|MultipleActiveResultSets' => false,'Encrypt' => false,|" /opt/infra/infra_php/InfraSqlServer.php
    fi

fi

set +e

RET=1
while [ ! "$RET" == "0" ]
do
    echo ""
    echo "Esperando a base de dados ficar disponivel... Vamos tentar chama-la ...."

    php -r "
    require_once '/opt/sip/web/Sip.php';
    \$conexao = BancoSip::getInstance();
    \$conexao->abrirConexao();
    \$conexao->executarSql('select sigla from sistema');"

    RET=$?
    sleep 3

done

set -e

echo "***************************************************"
echo "***************************************************"
echo "**GERACAO DE CERTIFICADO PARA O APACHE*************"
echo "***************************************************"
echo "***************************************************"

# Gera certificados caso necessario para desenvolvimento
if [ ! -d "/sei/certs/seiapp" ]; then
    echo "Diretorio /sei/certs/seiapp nao encontrado, criando ..."
    mkdir -p /sei/certs/seiapp
fi

echo "Verificando se certificados existem no diretorio /certs...."
if [ ! -f /sei/certs/seiapp/sei-ca.pem ] || [ ! -f /sei/certs/seiapp/sei.crt ]; then
    echo "Arquivos de cert nao encontrados criando auto assinados ..."

    cd /sei/certs/seiapp

    echo "Criando CA"
    openssl genrsa -out sei-ca-key.pem 2048
    openssl req -x509 -new -nodes -key sei-ca-key.pem \
        -days 10000 -out sei-ca.pem -subj "/CN=sei-dev"

    echo "Criando certificados para o dominio: $APP_HOST"
    openssl genrsa -out sei.key 2048
    openssl req -new -nodes -key sei.key \
        -days 10000 -out sei.csr -subj "/CN=$APP_HOST"
    openssl x509 -req -in sei.csr -CA sei-ca.pem \
        -CAkey sei-ca-key.pem -CAcreateserial \
        -out sei.crt -days 10000 -extensions v3_req

    cat /sei/certs/seiapp/sei-ca.pem >> /etc/ssl/certs/cacert.pem
    echo "Adicionada nova CA ao TrustStore\n"
else
    echo "Arquivos de cert encontrados vamos tentar utiliza-los..."
fi

cd /sei/certs/seiapp
cp sei.crt /etc/pki/tls/certs/sei.crt
cp sei-ca.pem /etc/pki/tls/certs/sei-ca.pem
cp sei.key /etc/pki/tls/private/sei.key
cat sei.crt sei.key >> /etc/pki/tls/certs/sei.pem

echo "Incluindo TrustStore no sistema"
#cp /icpbrasil/*.crt /etc/pki/ca-trust/source/anchors/
cp sei-ca.pem /etc/pki/ca-trust/source/anchors/

openssl genrsa -out localhost.key 2048
openssl req -new -key localhost.key  -out localhost.csr -subj "/CN=${APP_HOST}"
openssl x509 -req -days 365 -in localhost.csr  -signkey localhost.key  -out localhost.crt
mv localhost.key /etc/pki/tls/private/
mv localhost.crt /etc/pki/tls/certs/
rm -f localhost.csr

update-ca-trust extract
update-ca-trust enable

# ajustar sessao do php
if [ "${APP_MEMCACHED_SESSION}" == "true" ]; then
    echo "Ajustando sessao do php para rodar no memcached"
    sed -i "s|php_value\[session.save_handler\].*|php_value[session.save_handler] = memcached|g" /etc/php-fpm.d/www.conf
    sed -i "s|php_value\[session.save_path\].*|php_value[session.save_path] = \"${APP_MEMCACHED_HOST}:11211\"|g" /etc/php-fpm.d/www.conf
fi

echo "Atualizador finalizado procedendo com a subida do apache..."

#atualizar
/usr/sbin/php-fpm && /usr/sbin/httpd -DFOREGROUND &
sleep 3

echo "Apache no ar"

# necessario para testarmos ambientes com a data retroagida
git config --global http.sslVerify false

echo "***************************************************"
echo "***************************************************"
echo "*INICIANDO CONFIGURACOES DO MODULO DE ESTATISTICAS*"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_ESTATISTICAS_INSTALAR" == "true" ]; then

    if [ -z "$MODULO_ESTATISTICAS_VERSAO" ] || \
       [ -z "$MODULO_ESTATISTICAS_URL" ] || \
	   [ -z "$MODULO_ESTATISTICAS_SIGLA" ] || \
	   [ -z "$MODULO_ESTATISTICAS_CHAVE" ]; then
        echo "Informe as seguinte variaveis de ambiente no container:"
        echo "MODULO_ESTATISTICAS_VERSAO, MODULO_ESTATISTICAS_URL, MODULO_ESTATISTICAS_SIGLA, MODULO_ESTATISTICAS_CHAVE"

    else

        echo "Verificando existencia do modulo de estatisticas"
        if [ -d "/opt/sei/web/modulos/mod-sei-estatisticas" ]; then
            echo "Ja existe um diretorio para o modulo de estatisticas. Vamos assumir que o codigo la esteja integro"

        else
            echo "Copiando o modulo de estatisticas"
            cp -Rf /sei-modulos/mod-sei-estatisticas /opt/sei/web/modulos/
        fi

        cd /opt/sei/web/modulos/mod-sei-estatisticas
        git checkout $MODULO_ESTATISTICAS_VERSAO
        echo "Versao do Governanca eh agora: $MODULO_ESTATISTICAS_VERSAO"

        cd /opt/sei/

        sed -i "s#/\*novomodulo\*/#'MdEstatisticas' => 'mod-sei-estatisticas', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php
        sed -i "s#/\*extramodulesconfig\*/#'MdEstatisticas' => array('url' => '$MODULO_ESTATISTICAS_URL','sigla' => '$MODULO_ESTATISTICAS_SIGLA','chave' => '$MODULO_ESTATISTICAS_CHAVE'), /\*extramodulesconfig\*/#g" config/ConfiguracaoSEI.php

    fi

else

    echo "Variavel MODULO_ESTATISTICAS_INSTALAR nao setada para true, pulando configuracao..."

fi

echo "***************************************************"
echo "***************************************************"
echo "**CONFIGURANDO MODULO REST************************"
echo "***************************************************"
echo "***************************************************"
if [ "$MODULO_REST_INSTALAR" == "true" ]; then

    if [ -f /sei/controlador-instalacoes/instalado-modulo-rest.ok ]; then

        if [ -z "$MODULO_REST_VERSAO" ] || \
           [ -z "$MODULO_REST_URL_NOTIFICACAO" ] || \
	       [ -z "$MODULO_REST_ID_APP" ] || \
	       [ -z "$MODULO_REST_CHAVE" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_REST_VERSAO, MODULO_REST_URL_NOTIFICACAO, MODULO_REST_ID_APP, MODULO_REST_CHAVE"

        else

            echo "Verificando existencia do modulo rest"
            if [ -d "/opt/sei/web/modulos/mod-wssei" ]; then
                echo "Ja existe um diretorio para o modulo wssei. Vamos assumir que o codigo la esteja integro"

            else
                echo "Copiando o modulo rest"
                cp -Rf /sei-modulos/mod-wssei /opt/sei/web/modulos/
            fi

            cd /opt/sei/
            sed -i "s#/\*novomodulo\*/#'MdWsSeiRest' => 'wssei/', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php

            cd /opt/sei/config/mod-wssei/
            cp -f /opt/sei/web/modulos/mod-wssei/src/config/ConfiguracaoMdWSSEI.php .
            sed -i "s#MOD_WSSEI_URL_SERVICO_NOTIFICACAO#MODULO_REST_URL_NOTIFICACAO#g" ConfiguracaoMdWSSEI.php
            sed -i "s#MOD_WSSEI_ID_APP#MODULO_REST_ID_APP#g" ConfiguracaoMdWSSEI.php
            sed -i "s#MOD_WSSEI_CHAVE_AUTORIZACAO#MODULO_REST_CHAVE#g" ConfiguracaoMdWSSEI.php
            sed -i "s#MOD_WSSEI_TOKEN_SECRET#MODULO_REST_TOKEN_SECRET#g" ConfiguracaoMdWSSEI.php

        fi

    else

        echo "Arquivo de controle do Modulo REST encontrado pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_REST_INSTALAR nao setada para true, pulando configuracao..."

fi

echo "***************************************************"
echo "***************************************************"
echo "**CONFIGURANDO MODULO RESPOSTA*********************"
echo "***************************************************"
echo "***************************************************"
if [ "$MODULO_RESPOSTA_INSTALAR" == "true" ]; then

    if [ ! -f /sei/controlador-instalacoes/instalado-modulo-resposta.ok ]; then

        if [ -z "$MODULO_RESPOSTA_VERSAO" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_RESPOSTA_VERSAO"

        else

            echo "Verificando existencia do modulo resposta"
            if [ -d "/opt/sei/web/modulos/mod-sei-resposta" ]; then
                echo "Ja existe um diretorio para o modulo resposta. Vamos assumir que o codigo la esteja integro"

            else

                echo "Copiando o módulo gestao documental"
                cp -Rf /sei-modulos/mod-sei-resposta /opt/sei/web/modulos/

                cd /opt/sei/web/modulos/mod-sei-resposta/

                git checkout $MODULO_RESPOSTA_VERSAO
                echo "Versao do Resposta eh agora: $MODULO_RESPOSTA_VERSAO"

                make clean
                make dist
                cd ..
                mv mod-sei-resposta mod-sei-resposta.old

                cd mod-sei-resposta.old/dist/

                files=( *.zip )
                f="${files[0]}"

                mkdir temp
                cd temp
                mv ../$f .

                yes | unzip $f

                yes | cp -Rf sei sip /opt/

                rm -rf /opt/sei/web/modulos/mod-sei-resposta.old

            fi

            cd /opt/sei/
            sed -i "s#/\*novomodulo\*/#'MdRespostaIntegracao' => 'mod-sei-resposta', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php

        fi

    else

        echo "Arquivo de controle do Modulo RESPOSTA encontrado pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_RESPOSTA_INSTALAR nao setada para true, pulando configuracao..."

fi


echo "***************************************************"
echo "***************************************************"
echo "**CONFIGURANDO MODULO GESTAO DOC*******************"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_GESTAODOCUMENTAL_INSTALAR" == "true" ]; then

    if [ -f /sei/controlador-instalacoes/instalado-modulo-gestaodocumental.ok ]; then

        if [ -z "$MODULO_GESTAODOCUMENTAL_VERSAO" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_GESTAODOCUMENTAL_VERSAO"

        else

            echo "Verificando existencia do modulo gestao documental"
            if [ -d "/opt/sei/web/modulos/gestao-documental" ]; then
                echo "Ja existe um diretorio para o modulo gestao documental. Vamos assumir que o codigo la esteja integro"
                sed -i "s#/\*novomodulo\*/#'MdGestaoDocumentalIntegracao' => 'gestao-documental', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php

            else

              echo "Copiando o módulo gestao documental"
              cp -Rf /sei-modulos/mod-gestao-documental /opt/sei/web/modulos/

              cd /opt/sei/web/modulos/mod-gestao-documental/

              git checkout $MODULO_GESTAODOCUMENTAL_VERSAO
              echo "Versao do Gestao Documental eh agora: $MODULO_GESTAODOCUMENTAL_VERSAO"

              make clean
              make build
              cd ..
              mv mod-gestao-documental mod-gestao-documental.old

              cd mod-gestao-documental.old/dist/

              files=( *.zip )
              f="${files[0]}"

              mkdir temp
              cd temp
              mv ../$f .

              yes | unzip $f

              yes | cp -Rf sei sip /opt/

              cd /opt/sei/
              sed -i "s#/\*novomodulo\*/#'MdGestaoDocumentalIntegracao' => 'gestao-documental', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php
              rm -rf /opt/sei/web/modulos/mod-gestao-documental.old

            fi

        fi

    else
        sed -i "s#/\*novomodulo\*/#'MdGestaoDocumentalIntegracao' => 'gestao-documental', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php
        echo "Arquivo de controle do Modulo GESTAO DOCUMENTAL encontrado pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_GESTAODOCUMENTAL_INSTALAR nao setada para true, pulando configuracao..."

fi


echo "***************************************************"
echo "***************************************************"
echo "*INICIANDO CONFIGURACOES DO MODULO LOGIN UNICO*****"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_LOGINUNICO_INSTALAR" == "true" ]; then

    if [ -f /sei/controlador-instalacoes/instalado-modulo-loginunico.ok ]; then

        if [ -z "$MODULO_LOGINUNICO_VERSAO" ] || \
           [ -z "$MODULO_LOGINUNICO_CLIENTID" ] || \
           [ -z "$MODULO_LOGINUNICO_SECRET" ] || \
           [ -z "$MODULO_LOGINUNICO_URLPROVIDER" ] || \
           [ -z "$MODULO_LOGINUNICO_REDIRECTURL" ] || \
           [ -z "$MODULO_LOGINUNICO_URLLOGOUT" ] || \
           [ -z "$MODULO_LOGINUNICO_SCOPE" ] || \
           [ -z "$MODULO_LOGINUNICO_URLSERVICOS" ] || \
           [ -z "$MODULO_LOGINUNICO_URLREVALIDACAO" ] || \
           [ -z "$MODULO_LOGINUNICO_CIENTIDVALIDACAO" ] || \
           [ -z "$MODULO_LOGINUNICO_SECRETVALIDACAO" ] || \
           [ -z "$MODULO_LOGINUNICO_ORGAO" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_LOGINUNICO_VERSAO, MODULO_LOGINUNICO_CLIENTID, MODULO_LOGINUNICO_SECRET, MODULO_LOGINUNICO_URLPROVIDER, MODULO_LOGINUNICO_REDIRECTURL"
            echo "MODULO_LOGINUNICO_URLLOGOUT, MODULO_LOGINUNICO_SCOPE, MODULO_LOGINUNICO_URLSERVICOS, MODULO_LOGINUNICO_URLREVALIDACAO"
            echo "MODULO_LOGINUNICO_CIENTIDVALIDACAO, MODULO_LOGINUNICO_SECRETVALIDACAO, MODULO_LOGINUNICO_ORGAO, VAR_LOGINUNICO_REDIRECT_URL"

        else

            echo "Verificando existencia do modulo de loginunico"
            if [ -d "/opt/sei/web/modulos/loginunico" ]; then
                echo "Ja existe um diretorio para o modulo de loginunico. Vamos assumir que o codigo la esteja integro"

            else

              echo "Copiando o módulo de loginunico"
              cp -Rf /sei-modulos/mod-sei-loginunico /opt/sei/web/modulos/

              cd /opt/sei/web/modulos/mod-sei-loginunico/
              git checkout $MODULO_LOGINUNICO_VERSAO
              echo "Versao do LoginÚnico é agora: $MODULO_LOGINUNICO_VERSAO"

              cp envs/mysql.env .env
              cp envs/modulo.env .modulo.env
              make clean
              make build
              cd ..
              mv mod-sei-loginunico mod-sei-loginunico.old

              cd mod-sei-loginunico.old/dist/

              files=( *.zip )
              f="${files[0]}"

              mkdir temp
              cd temp
              mv ../$f .

              yes | unzip $f

              yes | cp -Rf sei sip /opt/

              cd /opt/sei/
              sed -i "s#/\*novomodulo\*/#'LoginUnicoIntegracao' => 'loginunico', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php

              cd /opt/sei/config/mod-loginunico/
              #cp ConfiguracaoModLoginUnico.exemplo.php ConfiguracaoModLoginUnico.php
              #nao quero o config de exemplo vou usar o config com as vars de ambiente e mudar o nome das vars
              cp /opt/sei/web/modulos/mod-sei-loginunico.old/src/config/ConfiguracaoModLoginUnico.php .
              sed -i "s#LOGIN_UNICO_CLIENT_ID#MODULO_LOGINUNICO_CLIENTID#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_SECRET#MODULO_LOGINUNICO_SECRET#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_URL_PROVIDER#MODULO_LOGINUNICO_URLPROVIDER#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_REDIRECT_URL#MODULO_LOGINUNICO_REDIRECTURL#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_URL_LOGOUT#MODULO_LOGINUNICO_URLLOGOUT#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_URL_SERVICOS#MODULO_LOGINUNICO_URLSERVICOS#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_REVALIDACAO_CLIENT_ID#MODULO_LOGINUNICO_CIENTIDVALIDACAO#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_REVALIDACAO_SECRET#MODULO_LOGINUNICO_SECRETVALIDACAO#g" ConfiguracaoModLoginUnico.php
              sed -i "s#LOGIN_UNICO_REVALIDACAO_URL#MODULO_LOGINUNICO_URLREVALIDACAO#g" ConfiguracaoModLoginUnico.php

              rm -rf /opt/sei/web/modulos/mod-sei-loginunico.old
            fi

        fi

    else

        echo "Arquivo de controle do Modulo de LoginUnico encontrado, provavelmente ja foi instalado, pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_LOGINUNICO_INSTALAR nao setada para true, pulando configuracao..."

fi


echo "***************************************************"
echo "***************************************************"
echo "INICIANDO CONFIGURACOES DO MODULO ASSINATURA AVANCADA*****"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_ASSINATURAVANCADA_INSTALAR" == "true" ]; then

    if [ -f /sei/controlador-instalacoes/instalado-modulo-assinaturavancada.ok ]; then

        if [ -z "$MODULO_ASSINATURAVANCADA_VERSAO" ] || \
           [ -z "$MODULO_ASSINATURAVANCADA_CLIENTID" ] || \
           [ -z "$MODULO_ASSINATURAVANCADA_SECRET" ] || \
           [ -z "$MODULO_ASSINATURAVANCADA_URLPROVIDER" ] || \
           [ -z "$MODULO_ASSINATURAVANCADA_URL_SERVICOS" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_ASSINATURAVANCADA_VERSAO, MODULO_ASSINATURAVANCADA_CLIENTID, MODULO_LOGINUNICO_SECRET, MODULO_LOGINUNICO_URLPROVIDER, MODULO_ASSINATURAVANCADA_URL_SERVICOS"

        else

            echo "Verificando existencia do modulo de assinatura avancada"
            if [ -d "/opt/sei/web/modulos/assinatura-avancada" ]; then
                echo "Ja existe um diretorio para o modulo de assinatura avancada. Vamos assumir que o codigo la esteja integro"

            else
              echo "Copiando o módulo de assinatura avancada"

              cd /opt/sei/web/modulos
              cp -R /sei-modulos/mod-sei-assinatura-avancada .
              cd mod-sei-assinatura-avancada/
              git checkout $MODULO_ASSINATURAVANCADA_VERSAO
              echo "Versao do LoginUnico eh agora: $MODULO_ASSINATURAVANCADA_VERSAO"

              cp envs/mysql.env .env
              cp envs/modulo.env .modulo.env
              make all
              cd ..
              mv mod-sei-assinatura-avancada mod-sei-assinatura-avancada.old

              cd mod-sei-assinatura-avancada.old/dist/

              files=( *.zip )
              f="${files[0]}"

              mkdir temp
              cd temp
              mv ../$f .

              yes | unzip $f

              yes | cp -Rf sei sip /opt/

              cd /opt/sei/
              sed -i "s#/\*novomodulo\*/#'AssinaturaAvancadaIntegracao' => 'assinatura-avancada', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php

              cd /opt/sei/config/mod-assinatura-avancada/
              #cp ConfiguracaoModAssinaturaAvancada.exemplo.php ConfiguracaoModAssinaturaAvancada.php
              #nao quero o config de exemplo vou usar o config com as vars de ambiente e mudar o nome das vars
              cp /opt/sei/web/modulos/mod-sei-assinatura-avancada.old/src/config/ConfiguracaoModAssinaturaAvancada.php .
              sed -i "s#ASSINATURA_URL_PROVIDER#MODULO_ASSINATURAVANCADA_URLPROVIDER#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#ASSINATURA_CLIENT_ID#MODULO_ASSINATURAVANCADA_CLIENTID#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#ASSINATURA_SECRET#MODULO_ASSINATURAVANCADA_SECRET#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#ASSINATURA_URL_SERVICOS#MODULO_ASSINATURAVANCADA_URL_SERVICOS#g" ConfiguracaoModAssinaturaAvancada.php

              sed -i "s#'VALIDAR_API_URL'#'MODULO_ASSINATURAVANCADA_VALIDAR_API_URL'#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#'VALIDAR_API_KEY'#'MODULO_ASSINATURAVANCADA_VALIDAR_API_KEY'#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#'INTEGRA_ICP_URL'#'MODULO_ASSINATURAVANCADA_INTEGRA_ICP_URL'#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#'INTEGRA_ICP_URL_CLEARINGS'#'MODULO_ASSINATURAVANCADA_INTEGRA_ICP_URL_CLEARINGS'#g" ConfiguracaoModAssinaturaAvancada.php
              sed -i "s#'INTEGRA_ICP_URL_ASSINAR'#'MODULO_ASSINATURAVANCADA_INTEGRA_ICP_URL_ASSINAR'#g" ConfiguracaoModAssinaturaAvancada.php

              rm -rf /opt/sei/web/modulos/mod-sei-assinatura-avancada.old

            fi


        fi

    else

        echo "Arquivo de controle do Modulo de Assinatura Avancada encontrado, provavelmente ja foi instalado, pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_ASSINATURAVANCADA_INSTALAR nao setada para true, pulando configuracao..."

fi

echo "***************************************************"
echo "***************************************************"
echo "*INICIANDO CONFIGURACOES DO MODULO PEN BARRAMENTO**"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_PEN_INSTALAR" == "true" ]; then

    if [ -f /sei/controlador-instalacoes/instalado-modulo-pen.ok ]; then

        if [ -z "$MODULO_PEN_VERSAO" ] || \
           [ -z "$MODULO_PEN_WEBSERVICE" ] || \
	         [ -z "$MODULO_PEN_CERTIFICADO_BASE64" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_PEN_VERSAO, MODULO_PEN_WEBSERVICE, MODULO_PEN_CERTIFICADO"

        else

            echo "Verificando existencia do modulo PEN"
            if [ -d "/opt/sei/web/modulos/pen" ]; then
                echo "Ja existe um diretorio para o modulo do PEN. Vamos assumir que o codigo la esteja integro"

            else

                echo "Buildando o módulo pen"

                cd /opt/sei/web/modulos
                cp -R /sei-modulos/mod-sei-pen mod-sei-pen
                cd mod-sei-pen
                git checkout $MODULO_PEN_VERSAO
                echo "Versao do PEN agora: $MODULO_PEN_VERSAO"
                make clean
                make build
                cd dist
                files=( *.zip )
                f="${files[0]}"
                mkdir temp
                cp $f temp/
                cd temp/
                yes | unzip $f
                cp -Rf sei/* /opt/sei/
                cp -Rf sip/* /opt/sip/

                cd /opt/sei/web/modulos
                mv mod-sei-pen mod-sei-pen.old

                cd /opt/sei/config/mod-pen/
                mv ./ConfiguracaoModPEN.exemplo.php ConfiguracaoModPEN.php
                sed -i "s#\"SenhaCertificado\" => \"\"#'SenhaCertificado' => $MODULO_PEN_CERTIFICADO_SENHA#g" ConfiguracaoModPEN.php
                sed -i "s#\"WebService\" => \"\"#'WebService' => $MODULO_PEN_WEBSERVICE#g" ConfiguracaoModPEN.php

                # adiciona o certificado
                cd /opt/sei/config/mod-pen
                echo -n $MODULO_PEN_CERTIFICADO_BASE64 | base64 -d > certificado.pem
                echo "certificado copiado"
                cat certificado.pem

            fi


        fi

    else

        echo "Arquivo de controle do Modulo PEN encontrado, provavelmente ja foi instalado, pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_PEN_INSTALAR nao setada para true, pulando configuracao..."

fi

echo "*****************************************************"
echo "*****************************************************"
echo "*INICIANDO CONFIGURACOES DO MODULO DE PETICIONAMENTO*"
echo "*****************************************************"
echo "*****************************************************"

if [ "$MODULO_PETICIONAMENTO_INSTALAR" == "true" ]; then

    if [ -f /sei/controlador-instalacoes/instalado-modulo-peticionamento.ok ]; then

        if [ -z "$MODULO_PETICIONAMENTO_VERSAO" ] || \
           [ -z "$MODULO_PETICIONAMENTO_URL" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_PETICIONAMENTO_VERSAO, MODULO_PETICIONAMENTO_URL"

        else

            echo "Verificando existencia do modulo de PETICIONAMENTO"
            if [ -d "/opt/sei/web/modulos/peticionamento" ]; then
                echo "Ja existe um diretorio para o modulo de PETICIONAMENTO. Vamos assumir que o codigo la esteja integro"

            else
                echo "Copiando o modulo de PETICIONAMENTO"
                cp -Rf /sei-modulos/peticionamento/* /opt
            fi

            cd /opt/sei/

            sed -i "s#/\*novomodulo\*/#'PeticionamentoIntegracao' => 'peticionamento', /\*novomodulo\*/#g" config/ConfiguracaoSEI.php

            cd /opt
            echo -ne "$APP_DB_SIP_USERNAME\n$APP_DB_SIP_PASSWORD\n" | php sip/scripts/sip_atualizar_versao_modulo_peticionamento.php
            echo -ne "$APP_DB_SEI_USERNAME\n$APP_DB_SEI_PASSWORD\n" | php sei/scripts/sei_atualizar_versao_modulo_peticionamento.php

            touch /sei/controlador-instalacoes/instalado-modulo-peticionamento.ok

        fi

    else

        echo "Arquivo de controle do Modulo de peticionamento encontrado, provavelmente ja foi instalado, pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_PETICIONAMENTO_INSTALAR nao setada para true, pulando configuracao..."

fi

echo "***************************************************"
echo "***************************************************"
echo "********MODULO PROTOCOLO INTEGRADO*****************"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_PI_INSTALAR" == "true" ]; then

    if [ ! -f /sei/controlador-instalacoes/instalado-modulo-pi.ok ]; then

        if [ -z "$MODULO_PI_VERSAO" ] || \
           [ -z "$MODULO_PI_URL" ] || \
           [ -z "$MODULO_PI_USUARIO" ] || \
           [ -z "$MODULO_PI_SENHA" ] || \
           [ -z "$MODULO_PI_EMAIL" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_PI_VERSAO, MODULO_PI_URL, MODULO_PI_USUARIO, MODULO_PI_SENHA, MODULO_PI_EMAIL"

        else

            echo "Verificando existencia do modulo PEN"
            if [ -d "/opt/sei/web/modulos/protocolo-integrado" ]; then
                echo "Ja existe um diretorio para o modulo do PI. Vamos assumir que o codigo la esteja integro"

            else

                echo "Buildando o modulo PI"

                cd /opt/sei/web/modulos
                cp -R /sei-modulos/mod-sei-protocolo-integrado mod-sei-protocolo-integrado
                cd mod-sei-protocolo-integrado
                git checkout $MODULO_PI_VERSAO
                echo "Versao do PI agora: $MODULO_PI_VERSAO"
                make clean
                make dist
                cd dist
                files=( *.zip )
                f="${files[0]}"
                mkdir temp
                cp $f temp/
                cd temp/
                yes | unzip $f
                \cp -Rf sei/* /opt/sei/
                \cp -Rf sip/* /opt/sip/

                cd /opt/sei/web/modulos
                mv mod-sei-protocolo-integrado mod-sei-protocolo-integrado.old

                cd /opt/sei/config/mod-protocolo-integrado/
                echo -ne "y" | mv ./ConfiguracaoModProtocoloIntegrado.exemplo.php ConfiguracaoModProtocoloIntegrado.php
                sed -i "s#\"WebService\" => \"\"#'WebService' => \"$MODULO_PI_URL\"#g" ConfiguracaoModProtocoloIntegrado.php
                sed -i "s#\"UsuarioWebService\" => \"\"#'UsuarioWebService' => \"$MODULO_PI_USUARIO\"#g" ConfiguracaoModProtocoloIntegrado.php
                sed -i "s#\"SenhaWebService\" => \"\"#'SenhaWebService' => \"$MODULO_PI_SENHA\"#g" ConfiguracaoModProtocoloIntegrado.php
                sed -i "s#\"PublicarProcessosRestritos\" => false#'PublicarProcessosRestritos' => true#g" ConfiguracaoModProtocoloIntegrado.php

            fi

        fi

    else

        echo "Arquivo de controle do Modulo PI encontrado, provavelmente ja foi instalado, pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_PI_INSTALAR nao setada para true, pulando configuracao..."

fi


echo "***************************************************"
echo "***************************************************"
echo "********MODULO INCOM*******************************"
echo "***************************************************"
echo "***************************************************"

if [ "$MODULO_INCOM_INSTALAR" == "true" ]; then

    if [ ! -f /sei/controlador-instalacoes/instalado-modulo-incom.ok ]; then

        if [ -z "$MODULO_INCOM_VERSAO" ]; then
            echo "Informe as seguinte variaveis de ambiente no container:"
            echo "MODULO_INCOM_VERSAO"

        else

            echo "Verificando existencia do modulo INCM"
            if [ -d "/opt/sei/web/modulos/incom" ]; then
                echo "Ja existe um diretorio para o modulo INCOM. Vamos assumir que o codigo la esteja integro"

            else

                echo "Buildando o modulo INCOM"

                cd /opt/sei/web/modulos
                cp -R /sei-modulos/mod-sei-incom mod-sei-incom
                cd mod-sei-incom
                git checkout $MODULO_INCOM_VERSAO
                echo "Versao do INCOM agora: $MODULO_INCOM_VERSAO"
                make clean
                make dist
                cd dist
                files=( *.zip )
                f="${files[0]}"
                mkdir temp
                cp $f temp/
                cd temp/
                yes | unzip $f
                \cp -Rf sei/* /opt/sei/
                \cp -Rf sip/* /opt/sip/

                cd /opt/sei/web/modulos
                mv mod-sei-incom mod-sei-incom.old

                cd /opt/sei/config/mod-incom/

            fi

        fi

    else

        echo "Arquivo de controle do Modulo INCOM encontrado, provavelmente ja foi instalado, pulando configuracao do modulo"

    fi

else

    echo "Variavel MODULO_INCOM_INSTALAR nao setada para true, pulando configuracao..."

fi

echo "***************************************************"
echo "Entrypoint chegou ao final..."
echo "Apache Liberado para uso"
echo "***************************************************"


tail -f /dev/null
