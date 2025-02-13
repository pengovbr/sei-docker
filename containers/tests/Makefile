SHELL := /bin/bash

ifneq ("$(wildcard envcontainers-test.env)","")
include envcontainers-test.env
endif


CONTAINERS_LIST=base-centos7 base-rocky93 haproxy traefik memcached jod jod4.4.8 mailcatcher base-mariadb10.5 mariadb10.5-sei40 base-mysql8 mysql8-sei41 base-sqlserver2019 sqlserver2019-sei40 sqlserver2019-sei41 base-oracle11g oracle11g-sei40 oracle11g-sei41 base-postgres15 postgres15-sei40 postgres15-sei41 solr8.2.0 solr9.4.0 solr9.6.1 base-app base-app-php8 app-dev app-dev-php8 app-ci app-ci-php8 app-ci-agendador app-ci-php8-agendador

help: ## Mostra essa ajuda. Voce pode usar tab para completar os comandos
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | sed -e 's|^\ ||'


getenv-test: ## target de apoio para copiar o envcontainers-teste.env a partir do modelo
		@cp -f envcontainers-test.env.modelo envcontainers-test.env
		@echo "Arquivo envcontainers-test.env criado com valores default.";
		@echo "Abra o arquivo e faca as alteracoes.";


envcontainers-test.env: ## target de apoio para verificar se existe o arquivo envcontainers-test.env
		@echo "Arquivo env nao encontrado para os testes nas operacoes de containeres."
		@echo "Ha um arquivo modelo (envcontainers-test.env.modelo) que vc pode usar para preencher com suas informacoes."
		@echo "Basta rodar make getenv-test"
		@echo "Em seguida, altere nesse arquivo as informacoes para os testes no seu ambiente"
		exit 1


verificar-arquivo-env-containers: ## target de apoio para verificar se existe um arquivo no diretorio containers e parar a execucao
		@if test -f ../envcontainers.env; then \
			echo "Um arquivo envcontainers.env foi encontrado em ../"; \
				echo "Apague esse arquivo antes de prosseguir, pois o teste usa o seu env proprio (envcontainers-test.env)"; \
				exit 1; \
		fi


## verificar-test-envs: target de apoio que roda na sequencia envcontainers-test.env verificar-arquivo-env-containers
verificar-test-envs: envcontainers-test.env verificar-arquivo-env-containers


## containers-erase: target de apoio apaga todos os conteineres
containers-erase: verificar-test-envs
		@echo "Vamos tentar apagar todos os conteineres locais com o registry $(DOCKER_REGISTRY) com a tag $(DOCKER_CONTAINER_VERSAO_PRODUTO)"
		@echo ""
		@make -C ../ DOCKER_REGISTRY=$(DOCKER_REGISTRY) DOCKER_CONTAINER_VERSAO_PRODUTO=$(DOCKER_CONTAINER_VERSAO_PRODUTO) erase-conteiners-local
		@echo ""
		@echo "Imagens apagadas. Vamos verificar se as imagens nao se encontram presentes"
		@for i in $(CONTAINERS_LIST); \
		do \
			echo "Verificando se a imagem: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) existe"; \
				if test -z "$$(docker images -q $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO))"; then \
					echo "ok! Imagem $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) nao existe."; \
				else \
					echo "Falha: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) continua existindo"; \
						exit 1; \
				fi; \
		done

## containers-build: target de apoio constroi todos os conteineres usando a tag test
containers-build: verificar-test-envs
		@echo "Vamos tentar buildar todos os conteineres usando o registry $(DOCKER_REGISTRY) com a tag $(DOCKER_CONTAINER_VERSAO_PRODUTO)"
		@echo ""
		@make -C ../ DOCKER_REGISTRY=$(DOCKER_REGISTRY) DOCKER_CONTAINER_VERSAO_PRODUTO=$(DOCKER_CONTAINER_VERSAO_PRODUTO) IMAGEM_BASE_CENTOS=$(IMAGEM_BASE_CENTOS) IMAGEM_BASE_CENTOS_VERSAO=$(IMAGEM_BASE_CENTOS_VERSAO) IMAGEM_BASE_MARIADB=$(IMAGEM_BASE_MARIADB) IMAGEM_BASE_MARIADB_VERSAO=$(IMAGEM_BASE_MARIADB_VERSAO) IMAGEM_BASE_SQLSERVER=$(IMAGEM_BASE_SQLSERVER) IMAGEM_BASE_SQLSERVER_VERSAO=$(IMAGEM_BASE_SQLSERVER_VERSAO) IMAGEM_BASE_ORACLE=$(IMAGEM_BASE_ORACLE) IMAGEM_BASE_ORACLE_VERSAO=$(IMAGEM_BASE_ORACLE_VERSAO) IMAGEM_BASE_POSTGRES=$(IMAGEM_BASE_POSTGRES) IMAGEM_BASE_POSTGRES_VERSAO=$(IMAGEM_BASE_POSTGRES_VERSAO) IMAGEM_BASE_APP=$(IMAGEM_BASE_APP) IMAGEM_BASE_APP_VERSAO=$(IMAGEM_BASE_APP_VERSAO) IMAGEM_BASE_APP_AGENDADOR=$(IMAGEM_BASE_APP_AGENDADOR) IMAGEM_BASE_APP_AGENDADOR_VERSAO=$(IMAGEM_BASE_APP_AGENDADOR_VERSAO) IMAGEM_BASE_PHPMEMCACHEDADMIN=$(IMAGEM_BASE_PHPMEMCACHEDADMIN) IMAGEM_BASE_PHPMEMCACHEDADMIN_VERSAO=$(IMAGEM_BASE_PHPMEMCACHEDADMIN_VERSAO) IMAGEM_BASE_OPENLDAP=$(IMAGEM_BASE_OPENLDAP) IMAGEM_BASE_OPENLDAP_VERSAO=$(IMAGEM_BASE_OPENLDAP_VERSAO) IMAGEM_BASE_HAPROXY=$(IMAGEM_BASE_HAPROXY) IMAGEM_BASE_HAPROXY_VERSAO=$(IMAGEM_BASE_HAPROXY_VERSAO) IMAGEM_BASE_TRAEFIK=$(IMAGEM_BASE_TRAEFIK) IMAGEM_BASE_TRAEFIK_VERSAO=$(IMAGEM_BASE_TRAEFIK_VERSAO) GITUSER_REPO_MODULOS=$(GITUSER_REPO_MODULOS) GITPASS_REPO_MODULOS=$(GITPASS_REPO_MODULOS) build-conteiners
		@echo ""
		@echo "Imagens criadas. Vamos verificar se as imagens encontram-se presentes"
		@for i in $(CONTAINERS_LIST); \
		do \
			echo "Verificando se a imagem: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) existe"; \
				if test -z "$$(docker images -q $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO))"; then \
					echo "Falha: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) nao existe"; \
						exit 1; \
				else \
					echo "ok! Imagem $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) existe."; \
				fi; \
		done


## containers-publish: target de apoio publica todos os conteineres usando a tag test
containers-publish: verificar-test-envs
		@echo "Vamos tentar publicar todos os conteineres usando o registry $(DOCKER_REGISTRY) com a tag $(DOCKER_CONTAINER_VERSAO_PRODUTO)"
		@echo ""
		@make -C ../ DOCKER_REGISTRY=$(DOCKER_REGISTRY) DOCKER_CONTAINER_VERSAO_PRODUTO=$(DOCKER_CONTAINER_VERSAO_PRODUTO) publish-containers
		@echo ""
		@echo "ok! Imagens publicadas."



## containers-download: target de apoio faz o pull em todos os conteineres
containers-download: verificar-test-envs
		@echo "Vamos agora tentar fazer o download das imagens de teste"
		@for i in $(CONTAINERS_LIST); \
		do \
			echo "Baixando a imagem: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO)"; \
				docker pull $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO); \
		done
		@echo "Imagens baixadas. Vamos verificar se as imagens encontram-se presentes"
		@for i in $(CONTAINERS_LIST); \
		do \
			echo "Verificando se a imagem: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) existe"; \
				if test -z "$$(docker images -q $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO))"; then \
					echo "Falha: $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) nao existe"; \
						exit 1; \
				else \
					echo "ok! Imagem $(DOCKER_REGISTRY)/$$i:$(DOCKER_CONTAINER_VERSAO_PRODUTO) existe."; \
				fi; \
		done


## test-containers: RODE ESSE TARGET PARA TESTAR TODA A STACK DE CRIACAO PUBLICACAO DOWNLOAD E ERASE
test-containers: verificar-test-envs
		@echo "Vamos iniciar a bateria de testes relacionada aos conteineres (erase, build, push e pull)"
		@echo "***********************************************************************************"
		@echo "Erase******************************************************************************"
		@echo "***********************************************************************************"
		make containers-erase
		@echo "***********************************************************************************"
		@echo "Build******************************************************************************"
		@echo "***********************************************************************************"
		make containers-build
		@echo "***********************************************************************************"
		@echo "Push*******************************************************************************"
		@echo "***********************************************************************************"
		make containers-publish
		@echo "***********************************************************************************"
		@echo "Erase Novamente********************************************************************"
		@echo "***********************************************************************************"
		make containers-erase
		@echo "***********************************************************************************"
		@echo "Download **************************************************************************"
		@echo "***********************************************************************************"
		make containers-download
		@echo "***********************************************************************************"
		@echo "Erase Novamente********************************************************************"
		@echo "***********************************************************************************"
		make containers-erase
		@echo "***********************************************************************************"
		@echo "Bateria de testes relacionadas aos conteineres finalizada**************************"
		@echo "***********************************************************************************"
