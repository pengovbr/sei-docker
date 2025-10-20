SHELL := /bin/bash

ifneq ($(shell docker compose version 2>/dev/null),)
DC=docker compose
else ifneq ($(shell docker-compose --version 2>/dev/null),)
DC=docker-compose
else
$(error ************  docker compose ou docker-compose nao encontrado. Ou inexistente para o usuario do make. Verifique... ************)
endif


ifneq ("$(wildcard env.env)","")
include env.env
endif

ifndef base
base=mysql-sei4
endif

ifndef HOST_URL
HOST_URL=http://localhost:8000
endif


define MENSAGEM_AVISO_FONTES
Nao foi possivel localizar o fonte do SEI.
Verifique o valor da variavel SEI_PATH
Vc pode definir um valor para a variavel SEI_PATH:
- adicionando SEI_PATH=caminhodosfontes ao seu env.env ou
- exportando a var rodadno o seguinte comando: export SEI_PATH=caminhodosfontes

endef
export MENSAGEM_AVISO_FONTES

define MENSAGEM_CONFIG
Valores validos no parametro base:
mysql-sei4 (default)
mysql-sei5
oracle-sei4
oracle-sei5
postgres-sei4
postgres-sei5
sqlserver-sei4
sqlserver-sei5

endef
export MENSAGEM_CONFIG


CMD_CURL_SEI_LOGIN = curl -s -L $(HOST_URL)/sei | grep "txtUsuario"
CMD_CURL_APACHE = curl -s -L $(HOST_URL)/sei | grep "Sistema"
CMD_INSTALACAO_SEI = echo -ne '$(SEI_DATABASE_USER_SCRIPT)\n$(SEI_DATABASE_PASSWORD_SCRIPT)\n' | php atualizar_versao_sei.php
CMD_INSTALACAO_SIP = echo -ne '$(SIP_DATABASE_USER_SCRIPT)\n$(SIP_DATABASE_PASSWORD_SCRIPT)\n' | php atualizar_versao_sip.php
CMD_INSTALACAO_RECURSOS_SEI = echo -ne '$(SIP_DATABASE_USER_SCRIPT)\n$(SIP_DATABASE_PASSWORD_SCRIPT)\n' | php atualizar_recursos_sei.php

help: ## Mostra essa ajuda. Voce pode usar tab para completar os comandos
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | sed -e 's|^\ ||'

env.env: ## Target de apoio. Carrega o arquivo env de acordo com a base escolhida
	@if [ ! -f "env.env" ]; then \
	cp envs/env-$(base).env env.env; \
	echo "Arquivo env.env nao existia. Copiado o arquivo default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de subir o ambiente."; \
	echo ""; sleep 5; \
	fi


check-sei-path:  ## Target de apoio. Verifica se o codigo do SEI existe no local indicado
	@if [ ! -f $(SEI_PATH)/sei/web/SEI.php ]; then \
	echo "$${MENSAGEM_AVISO_FONTES}" ; \
	echo ""; \
	exit 1 ; \
	fi


# acessa o SEI e verifica se esta respondendo a tela de login
check-sei-isalive: ## Target de apoio. Verifica se o SEI respondeu a tela de login
	@echo ""
	@echo "Vamos tentar acessar a pagina de login do SEI, vamos aguardar por 45 segs."
	@for number in 1 2 3 4 5 6 7 8 9 ; do \
		echo 'Tentando acessar...'; var=$$(echo $$($(CMD_CURL_SEI_LOGIN))); \
			if [ "$$var" != "" ]; then \
					echo 'Pagina respondeu com tela de login' ; \
					echo 'Acesse com $(HOST_URL)/sei ou $(HOST_URL)/sip' ; \
					break ; \
			else \
				echo 'Aguardando resposta ...'; \
			fi; \
			sleep 5; \
	done

# acessa o SEI e verifica se esta aparecendo alguma pagina, mesmo q erro. Necessario para saber se podemos rodar update
check-sei-ispinging: ## Target de apoio. Verifica se algo apareceu na tela de login mesmo q erro
	@echo ""
	@echo "Verificando se apache subiu..."
	@for number in 1 2 3 4 5 6 7 8 9 ; do \
		echo 'Tentando acessar...'; var=$$(echo $$($(CMD_CURL_APACHE))); \
			if [ "$$var" != "" ]; then \
					echo 'Apache respondeu com algo' ; \
					break ; \
			else \
				echo 'Aguardando resposta ...'; \
			fi; \
			sleep 5; \
	done

## prerequisites-up: Target de apoio. Encadeia env.env e check-sei-path
prerequisites-up: env.env check-sei-path


## up: Sobe o projeto, mas antes verifica os pre-requisitos
up: prerequisites-up
	$(DC) --env-file env.env up -d
	make check-sei-ispinging

## up-update: Sobe o projeto, e roda o update na sequencia
up-update: prerequisites-up
	$(DC) --env-file env.env up -d
	make check-sei-ispinging
	make update
	make check-sei-isalive

## update: Roda arquivos de update de versao para o sei e sip. Caso fonte na v4.1 e bd na 4.0, rode este target apos o up
update: prerequisites-up
	$(DC) --env-file env.env run --rm -w /opt/sip/scripts/ httpd bash -c "$(CMD_INSTALACAO_SIP)"; true
	$(DC) --env-file env.env run --rm -w /opt/sip/scripts/ httpd bash -c "$(CMD_INSTALACAO_RECURSOS_SEI)"; true
	$(DC) --env-file env.env run --rm -w /opt/sei/scripts/ httpd bash -c "$(CMD_INSTALACAO_SEI)"; true
	make check-sei-isalive

config: ## sobreescreve o arquivo env.env para uma nova base de dados / versao do sei. Ex: make base=mysql-sei4. Digite make config para listar os valores possiveis
	@echo "$${MENSAGEM_CONFIG}" ; \
	if [ "$(base)" = "mysql-sei4" ] || \
	   [ "$(base)" = "mysql-sei5" ] || \
	   [ "$(base)" = "oracle-sei4" ] || \
	   [ "$(base)" = "oracle-sei5" ] || \
	   [ "$(base)" = "postgres-sei4" ] || \
	   [ "$(base)" = "postgres-sei5" ] || \
	   [ "$(base)" = "sqlserver-sei4" ] || \
	   [ "$(base)" = "sqlserver-sei5" ]; then \
		cp -f envs/env-$(base).env env.env; \
		echo "Ambiente configurado para utilizar a base de dados/versao SEI $(base)"; \
	else \
		echo "Valor invalido para o parametro base, verifique acima os valores possiveis"; \
	fi;

down: ## apenas para o projeto com possibilidade de subir novamente mais tarde
	$(DC) --env-file env.env down


## restart: Roda os targets down e up na sequencia
restart: down up


## destroy: destroi os componentes e também apaga os volumes de dados
destroy: env.env
	$(DC) --env-file env.env down --volumes
