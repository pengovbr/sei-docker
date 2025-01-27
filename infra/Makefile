
include envlocal.env

ifeq ("$(MAKEFILE_MODO_VERBOSE)",  "true")
SHELL = sh -xv
endif

ifneq ($(shell docker compose version 2>/dev/null),)
DC=docker compose
else ifneq ($(shell docker-compose --version 2>/dev/null),)
DC=docker-compose
else
$(error ************  docker compose ou docker-compose nao encontrado. Ou inexistente para o usuario do make. Verifique... ************)
endif

EXISTE_LOCAL_DB := $(shell docker volume ls | grep $(VOLUME_DB)$$ )
EXISTE_LOCAL_FONTES := $(shell docker volume ls | grep $(VOLUME_FONTES)$$ )
EXISTE_LOCAL_ARQUIVOS_EXTERNOS := $(shell docker volume ls | grep $(VOLUME_ARQUIVOSEXTERNOS)$$ )
EXISTE_LOCAL_SOLR := $(shell docker volume ls | grep $(VOLUME_SOLR)$$ )
EXISTE_LOCAL_OPENLDAP_SLAPD := $(shell docker volume ls | grep $(VOLUME_OPENLDAP_SLAPD)$$ )
EXISTE_LOCAL_OPENLDAP_DB := $(shell docker volume ls | grep $(VOLUME_OPENLDAP_DB)$$ )
EXISTE_LOCAL_CONTROLADOR_INSTALACAO := $(shell docker volume ls | grep $(VOLUME_CONTROLADOR_INSTALACAO)$$ )
EXISTE_LOCAL_CERTS := $(shell docker volume ls | grep $(VOLUME_CERTS)$$ )

qtd := "2"
LOGS_SEGUIR=true

DIR := ${CURDIR}

CMDCOMPOSE = $(DC) -f orquestrators/docker-compose/docker-compose.yml
CMD_CURL_SEI_LOGIN = curl -s -L -k $(APP_PROTOCOLO)://$(APP_HOST)/sei | grep "txtUsuario"

check_isalive-timeout := "37"
kube_timeout := "180s"

help:   ## Lista de comandos disponiveis e descricao. Voce pode usar TAB para completar os comandos
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'


check-sei-isalive: ## Target de apoio. Acessa o SEI e verifica se esta respondendo a tela de login
	@echo "Vamos tentar acessar a pagina de login do SEI, vamos aguardar ate 95 segs."
	@for number in $$(seq 1 $(check_isalive-timeout)); do \
	    echo 'Tentando acessar...'; var=$$(echo $$($(CMD_CURL_SEI_LOGIN))); \
			if [ "$$var" != "" ]; then \
					echo 'Pagina respondeu com tela de login' ; \
					break ; \
			else \
			    echo 'Aguardando resposta'; \
			fi; \
			sleep 5; \
	done; \
	var=$$(echo $$($(CMD_CURL_SEI_LOGIN))); \
	if [ "$$var" == "" ]; then echo 'Pagina de login nao respondeu. Verifique. Abandonando execucao'; exit 1 ; fi;


check-fontes-posicionado: ## Verifica se o fonte esta posicionado
	@if [ ! -f $(LOCALIZACAO_FONTES_SEI)/sip/web/Sip.php ]; then "Codigo fonte sip nao localizado em $(LOCALIZACAO_FONTES_SEI)."; exit 1; fi
	@if [ ! -f $(LOCALIZACAO_FONTES_SEI)/sei/web/SEI.php ]; then "Codigo fonte sei nao localizado em $(LOCALIZACAO_FONTES_SEI)."; exit 1; fi
	@if [ ! -f $(LOCALIZACAO_FONTES_SEI)/infra/infra_php/InfraRN.php ]; then "Codigo fonte infra nao localizado em $(LOCALIZACAO_FONTES_SEI)."; exit 1; fi

check-version-compatibility: ## Verifica a compatibilidade das versoes com os conteineres
	@v=$$(grep -o "const SEI_VERSAO = '.*';" $(LOCALIZACAO_FONTES_SEI)/sei/web/SEI.php | sed "s|const SEI_VERSAO = '||" | sed "s|';||"); \
	set +e; echo $$v |  grep "5\..*\..*" > /dev/null; e=$$?; set -e; \
	set +e; echo $$DOCKER_IMAGE_APP |  grep "php8" > /dev/null; e1=$$?; set -e; \
	set +e; echo $$DOCKER_IMAGE_SOLR |  grep "solr9" > /dev/null; e2=$$?; set -e; \
	set +e; echo $$DOCKER_IMAGE_BD |  grep -E "(-sei41|-sei50)" > /dev/null; e3=$$?; set -e; \
	if [ "$$e" = "0" ]; then \
		if [ ! "$$e1" = "0" ] || [ ! "$$e2" = "0" ] || [ ! "$$e3" = "0" ]; then \
		    echo "Vc escolheu o SEI5. Use as imagens correspondentes para o SEI5. Ver arquivos env de exemplo."; \
			echo "Aparentemente as imagens de app, banco ou solr estao erradas. Verifique."; \
			echo "Mensagem informativa. Iremos continuar em 10 segundos"; \
			sleep 10; \
		fi; \
	else \
		if [ "$$e1" = "0" ] || [ "$$e2" = "0" ] || [ "$$e3" = "0" ]; then \
		    echo "Vc escolheu o SEI4. Use as imagens correspondentes para o SEI4. Ver arquivos env de exemplo."; \
			echo "Aparentemente as imagens de app, banco ou solr estao erradas. Verifique."; \
			echo "Mensagem informativa. Iremos continuar em 10 segundos"; \
			sleep 10; \
		fi; \
	fi;

criar_volumes: ## Cria todos os volumes necessarios ao projeto. As vezes eh necessario apagar os volumes antes, para apagar make apagar_volumes
	make criar_volume_fontes
	make criar_volume_certs
	make criar_volume_banco
	make criar_volume_arquivos_externos
	make criar_volume_solr
	make criar_volume_openldap
	make criar_volume_controlador_instalacao


criar_volume_fontes: check-fontes-posicionado ## Cria o volume docker com os fontes que serao consumidos pelo projeto
ifneq ("$(EXISTE_LOCAL_FONTES)",  "")
	@echo "Ja existe um volume de Fontes. Voce pode apaga-lo com o comando make apagar_volume_fontes"
else
	docker run --rm -v $(LOCALIZACAO_FONTES_SEI):/source -v $(VOLUME_FONTES):/opt -w /source alpine sh -c "cp -R infra sei sip /opt/"
endif


criar_volume_certs: ## Cria o volume docker com os certs que serao consumidos pelo projeto
ifneq ("$(EXISTE_LOCAL_CERTS)",  "")
	@echo "Ja existe um volume de Certificados. Voce pode apaga-lo com o comando make apagar_volume_certs"
else
	docker run --rm -v $(LOCALIZACAO_CERTS):/source -v $(VOLUME_CERTS):/destino -w /source --entrypoint="" processoeletronico/haproxy:latest sh -c "echo ''; echo ''; echo ''; if [[ -f cert0.pem ]] ; then echo 'Cert para o balanceador ja existe, pulando.';echo ''; echo ''; echo '';  else echo 'Cert para o balanceador nao existe. Copiando auto assinado default...' ; cp /mycertexample/cert0.pem /destino ; echo 'Copiado';echo ''; echo ''; echo '';  fi"
	docker run --rm -v $(LOCALIZACAO_CERTS):/source -v $(VOLUME_CERTS):/destino -w /source --entrypoint="" processoeletronico/haproxy:latest sh -c "if [ ! -f seiapp/sei-ca.pem ] || [ ! -f seiapp/sei.crt ] || [ ! -f seiapp/sei.key ]; then echo ''; echo ''; echo ''; echo 'CA, cert ou key nao existe para o SEI. O conteiner de app vai criar um auto assinado. Nao se preocupe caso esteja usando um cert valido no balanceador'; echo ''; echo ''; echo '';  else echo ''; echo ''; echo ''; echo 'CA, cert e key encontradas para o SEI, pulando.';  echo ''; echo ''; echo '';  fi"

endif

criar_volume_banco: ## Cria o volume docker para o banco de dados que serao consumidos pelo projeto

ifneq ("$(EXISTE_LOCAL_DB)",  "")
	@echo "Ja existe um volume de banco de dados. Voce pode apaga-lo com o comando make apagar_volume_banco"
else
	docker volume create $(VOLUME_DB)
endif

criar_volume_arquivos_externos: ## Cria o volume docker para os arquivos externos

ifneq ("$(EXISTE_LOCAL_ARQUIVOS_EXTERNOS)",  "")
	@echo "Ja existe um volume de Arquivos Externos. Voce pode apaga-lo com o comando make apagar_volume_arquivos_externos"
else
	docker volume create $(VOLUME_ARQUIVOSEXTERNOS)
endif

criar_volume_solr: ## Cria o volume docker para os dados do solr

ifneq ("$(EXISTE_LOCAL_SOLR)",  "")
	@echo "Ja existe um volume para o Solr. Voce pode apaga-lo com o comando make apagar_volume_solr"
else
	docker volume create $(VOLUME_SOLR)
endif

criar_volume_openldap_slapd: ## Cria o volume docker para a base do openldap slapd

ifneq ("$(EXISTE_LOCAL_OPENLDAP_SLAPD)",  "")
	@echo "Ja existe um volume de openldap slapd. Voce pode apaga-lo com o comando make apagar_volume_openldap_slapd"
else
	docker volume create $(VOLUME_OPENLDAP_SLAPD)
endif

criar_volume_openldap_db: ## Cria os volumes docker para a base do openldap db

ifneq ("$(EXISTE_LOCAL_OPENLDAP_DB)",  "")
	@echo "Ja existe um volume de openldap db. Voce pode apaga-lo com o comando make apagar_volume_openldap_db"
else
	docker volume create $(VOLUME_OPENLDAP_DB)
endif

criar_volume_openldap: criar_volume_openldap_slapd criar_volume_openldap_db

criar_volume_controlador_instalacao: ## Cria o volume para controlar a instalacao do SEI e modulos

ifneq ("$(EXISTE_LOCAL_CONTROLADOR_INSTALACAO)",  "")
	@echo "Ja existe um volume de controlador da instalacao. Voce pode apaga-lo com o comando make apagar_volume_controlador_instalacao"
else
	docker volume create $(VOLUME_CONTROLADOR_INSTALACAO)
endif


build_docker_compose: ## Construa o docker-compose.yml baseado no arquivo envlocal.env
	rm -f orquestrators/docker-compose/docker-compose.yml

	envsubst < orquestrators/docker-compose/docker-compose-template.yml > orquestrators/docker-compose/docker-compose.yml
	@echo "Agora vamos iniciar uma serie de substituicoes de variaveis para montar o docker-compose.yml"
	@echo "Os comandos sed nao aparecem aqui na tela."
	@echo "Caso deseje que eles aparecam ative no envlocal.env o modo Debug"


ifeq ("$(APP_PORTA_80_MAP_EXPOR)",  "true")
	@sed -i'' -e "s|#ports:|ports:|" orquestrators/docker-compose/docker-compose.yml

ifneq ("$(APP_PORTA_80_MAP_EXPOR)",  "")
	@sed -i'' -e "s|#    - $(APP_PORTA_80_MAP)|    - $(APP_PORTA_80_MAP)|" orquestrators/docker-compose/docker-compose.yml
else
    @sed -i'' -e "|#    - $(APP_PORTA_80_MAP)|d" orquestrators/docker-compose/docker-compose.yml
endif
	@sed -i'' -e "s|nada|nada|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(APP_PORTA_443_MAP_EXPOR)",  "true")
	@sed -i'' -e "s|#ports:|ports:|" orquestrators/docker-compose/docker-compose.yml

ifneq ("$(APP_PORTA_443_MAP_EXPOR)",  "")
	@sed -i'' -e "s|#    - $(APP_PORTA_443_MAP)|    - $(APP_PORTA_443_MAP)|" orquestrators/docker-compose/docker-compose.yml
else
    @sed -i'' -e "|#    - $(APP_PORTA_443_MAP)|d" orquestrators/docker-compose/docker-compose.yml
endif
	@sed -i'' -e "s|nada|nada|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(APP_PROTOCOLO)",  "https")
	@sed -i'' -e "s|#- EXCLUDE_PORTS=80|- EXCLUDE_PORTS=80|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#- EXTRA_ROUTE_SETTINGS=ssl verify none|- EXTRA_ROUTE_SETTINGS=ssl verify none|" orquestrators/docker-compose/docker-compose.yml
endif
ifeq ("$(APP_PROTOCOLO)",  "http")
	@sed -i'' -e "s|#- EXCLUDE_PORTS=443|- EXCLUDE_PORTS=443|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(DBADMIN_PRESENTE)",  "true")
	@sed -i'' -e "s|#dbadmin: #servicedbadmin|dbadmin: #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_DBADMIN} #servicedbadmin|    image: ${DOCKER_IMAGE_DBADMIN} #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    labels:  #servicedbadmin|    labels:  #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.enable: true  #servicedbadmin|        traefik.enable: true  #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.httpdbadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/dbadmin\`) #servicedbadmin|        traefik.http.routers.httpdbadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/dbadmin\`) #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.dbadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/dbadmin\`) #servicedbadmin|        traefik.http.routers.dbadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/dbadmin\`) #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.dbadmin.tls: true #servicedbadmin|        traefik.http.routers.dbadmin.tls: true #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.dbadmin.loadbalancer.server.scheme: http #servicedbadmin|        traefik.http.services.dbadmin.loadbalancer.server.scheme: http #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.dbadmin.loadbalancer.server.port: 80 #servicedbadmin|        traefik.http.services.dbadmin.loadbalancer.server.port: 80 #servicedbadmin|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(MEMCACHEDADMIN_PRESENTE)",  "true")

	@sed -i'' -e "s|#memcachedadmin: #servicememcachedadmin|memcachedadmin: #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_MEMCACHEDADMIN} #servicememcachedadmin|    image: ${DOCKER_IMAGE_MEMCACHEDADMIN}  #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    labels: #servicememcachedadmin|    labels: #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.enable: true #servicememcachedadmin|        traefik.enable: true #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.httpmemcachedadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/memcachedadmin\`) #servicememcachedadmin|        traefik.http.routers.httpmemcachedadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/memcachedadmin\`) #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.memcachedadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/memcachedadmin\`) #servicememcachedadmin|        traefik.http.routers.memcachedadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/memcachedadmin\`) #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.memcachedadmin.tls: true #servicememcachedadmin|        traefik.http.routers.memcachedadmin.tls: true #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.memcachedadmin.loadbalancer.server.scheme: http #servicememcachedadmin|        traefik.http.services.memcachedadmin.loadbalancer.server.scheme: http #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.memcachedadmin.loadbalancer.server.port: 80 #servicememcachedadmin|        traefik.http.services.memcachedadmin.loadbalancer.server.port: 80 #servicememcachedadmin|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(JOD_PRESENTE)",  "true")
	@sed -i'' -e "s|#jod: #servicejod|jod: #servicejod|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_JOD} #servicejod|    image: ${DOCKER_IMAGE_JOD} #servicejod|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#- jod:jod #servicejod|- jod:jod #servicejod|g" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(MAIL_CATCHER_PRESENTE)",  "true")
	@sed -i'' -e "s|#mail: #servicemail|mail: #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_MAIL} #servicemail|    image: ${DOCKER_IMAGE_MAIL} #servicemail|"	 orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e 's|#    command: \["mailcatcher", "--no-quit", "--foreground", "--ip=0.0.0.0", "--smtp-port=25", "--http-port=80", "--http-path=/mailadmin"\] #servicemail|    command: ["mailcatcher", "--no-quit", "--foreground", "--ip=0.0.0.0", "--smtp-port=25", "--http-port=80", "--http-path=/mailadmin"] #servicemail|'	 orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    expose: #servicemail|    expose: #servicemail|"	 orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - 25 #servicemail|        - 25 #servicemail|"	 orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - 80 #servicemail|        - 80 #servicemail|"	 orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    labels: #servicemail|    labels: #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.enable: true #servicemail|        traefik.enable: true #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.httpmailadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/mailadmin\`) #servicemail|        traefik.http.routers.httpmailadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/mailadmin\`) #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.mailadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/mailadmin\`) #servicemail|        traefik.http.routers.mailadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/mailadmin\`) #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.mailadmin.tls: true #servicemail|        traefik.http.routers.mailadmin.tls: true #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.mailadmin.loadbalancer.server.scheme: http #servicemail|        traefik.http.services.mailadmin.loadbalancer.server.scheme: http #servicemail|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.mailadmin.loadbalancer.server.port: 80 #servicemail|        traefik.http.services.mailadmin.loadbalancer.server.port: 80 #servicemail|" orquestrators/docker-compose/docker-compose.yml

endif

ifeq ("$(OPENLDAP_PRESENTE)",  "true")
	@sed -i'' -e "s|#ldapadmin: #serviceldap|ldapadmin: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_OPENLDAP_PHPLDAPADMIN} #serviceldap|    image: ${DOCKER_IMAGE_OPENLDAP_PHPLDAPADMIN} #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    environment: #serviceldap|    environment: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - PHPLDAPADMIN_LDAP_CLIENT_TLS=false #serviceldap|        - PHPLDAPADMIN_LDAP_CLIENT_TLS=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - PHPLDAPADMIN_LDAP_HOSTS=openldap #serviceldap|        - PHPLDAPADMIN_LDAP_HOSTS=openldap #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - PHPLDAPADMIN_HTTPS=false #serviceldap|        - PHPLDAPADMIN_HTTPS=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - PHPLDAPADMIN_TRUST_PROXY_SSL=true #serviceldap|        - PHPLDAPADMIN_TRUST_PROXY_SSL=true #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    labels: #serviceldap|    labels: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.enable: true #serviceldap|        traefik.enable: true #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.httpldapadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/phpldapadmin\`) #serviceldap|        traefik.http.routers.httpldapadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/phpldapadmin\`) #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.ldapadmin.rule: Host(\`${APP_HOST}\`) && PathPrefix(\`/phpldapadmin\`) #serviceldap|        traefik.http.routers.ldapadmin.rule: Host(\`${APP_HOST}\`) \&\& PathPrefix(\`/phpldapadmin\`) #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.routers.ldapadmin.tls: true #serviceldap|        traefik.http.routers.ldapadmin.tls: true #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.ldapadmin.loadbalancer.server.scheme: http #serviceldap|        traefik.http.services.ldapadmin.loadbalancer.server.scheme: http #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        traefik.http.services.ldapadmin.loadbalancer.server.port: 80 #serviceldap|        traefik.http.services.ldapadmin.loadbalancer.server.port: 80 #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    links: #serviceldap|    links: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - openldap:openldap #serviceldap|        - openldap:openldap #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#openldap: #serviceldap|openldap: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_OPENLDAP} #serviceldap|    image: ${DOCKER_IMAGE_OPENLDAP} #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    environment: #serviceldap|    environment: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - KEEP_EXISTING_CONFIG=false #serviceldap|        - KEEP_EXISTING_CONFIG=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_ADMIN_PASSWORD=${OPENLDAP_ADMIN_PASSWORD} #serviceldap|        - LDAP_ADMIN_PASSWORD=${OPENLDAP_ADMIN_PASSWORD} #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_BACKEND=mdb #serviceldap|        - LDAP_BACKEND=mdb #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_BASE_DN= #serviceldap|        - LDAP_BASE_DN= #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_CONFIG_PASSWORD=configldap #serviceldap|        - LDAP_CONFIG_PASSWORD=configldap #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_DOMAIN=pen.gov.br #serviceldap|        - LDAP_DOMAIN=pen.gov.br #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_LOG_LEVEL=256 #serviceldap|        - LDAP_LOG_LEVEL=256 #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_ORGANISATION=Processo Eletronico Nacional #serviceldap|        - LDAP_ORGANISATION=Processo Eletronico Nacional #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_READONLY_USER=false #serviceldap|        - LDAP_READONLY_USER=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_REMOVE_CONFIG_AFTER_SETUP=true #serviceldap|        - LDAP_REMOVE_CONFIG_AFTER_SETUP=true #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_REPLICATION=false #serviceldap|        - LDAP_REPLICATION=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_RFC2307BIS_SCHEMA=false #serviceldap|        - LDAP_RFC2307BIS_SCHEMA=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_SSL_HELPER_PREFIX=ldap #serviceldap|        - LDAP_SSL_HELPER_PREFIX=ldap #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - LDAP_TLS=false #serviceldap|        - LDAP_TLS=false #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    volumes_from: #serviceldap|    volumes_from: #serviceldap|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - storage-openldap #serviceldap|        - storage-openldap #serviceldap|" orquestrators/docker-compose/docker-compose.yml

endif


ifeq ("$(BALANCEADOR_PRESENTE)",  "true")
	@sed -i'' -e "s|#balanceador: #servicebal|balanceador: #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    image: ${DOCKER_IMAGE_BALANCEADOR} #servicebal|    image: ${DOCKER_IMAGE_BALANCEADOR} #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    links: #servicebal|    links: #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - app #servicebal|        - app #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - solr #servicesolr #servicebal|        - solr #servicesolr #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    command: #servicebal|    command: #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --log.level=DEBUG #servicebal|        - --log.level=DEBUG #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --api.dashboard=true #servicebal|        - --api.dashboard=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --providers.docker=true #servicebal|        - --providers.docker=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --providers.docker.exposedbydefault=false #servicebal|        - --providers.docker.exposedbydefault=false #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --accesslog=true #servicebal|        - --accesslog=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --entrypoints.websecure.address=:443 #servicebal|        - --entrypoints.websecure.address=:443 #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --entrypoints.web.address=:80 #servicebal|        - --entrypoints.web.address=:80 #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --serverstransport.insecureskipverify=true #servicebal|        - --serverstransport.insecureskipverify=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - --providers.file.directory=/etc/traefik/dynamic_conf #servicebal|        - --providers.file.directory=/etc/traefik/dynamic_conf #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    labels: #servicebal|    labels: #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.enable=true #servicebal|          - traefik.enable=true #servicebal|" orquestrators/docker-compose/docker-compose.yml

	@sed -i'' -e "s|#          - traefik.http.routers.seiredirect.rule=Host(\`${APP_HOST}\`) && Path(\`/sei\`) #servicebal|          - traefik.http.routers.seiredirect.rule=Host(\`${APP_HOST}\`) \&\& Path(\`/sei\`) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.seiredirect.middlewares=seiredirectrule #servicebal|          - traefik.http.routers.seiredirect.middlewares=seiredirectrule #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.seiredirectrule.redirectregex.regex=.* #servicebal|          - traefik.http.middlewares.seiredirectrule.redirectregex.regex=.* #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.seiredirectrule.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|          - traefik.http.middlewares.seiredirectrule.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.sipredirect.rule=Host(\`${APP_HOST}\`) && Path(\`/sip\`) #servicebal|          - traefik.http.routers.sipredirect.rule=Host(\`${APP_HOST}\`) \&\& Path(\`/sip\`) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.sipredirect.middlewares=sipredirectrule #servicebal|          - traefik.http.routers.sipredirect.middlewares=sipredirectrule #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.sipredirectrule.redirectregex.regex=.* #servicebal|          - traefik.http.middlewares.sipredirectrule.redirectregex.regex=.* #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.sipredirectrule.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|          - traefik.http.middlewares.sipredirectrule.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.sipredirectrule.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|          - traefik.http.middlewares.sipredirectrule.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashroot.rule=Host(\`${APP_HOST}\`) && (PathPrefix(\`/traefik\`)) #servicebal|          - traefik.http.routers.mydashroot.rule=Host(\`${APP_HOST}\`) \&\& (PathPrefix(\`/traefik\`)) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashroot.middlewares= mydashredirectroothttp #servicebal|          - traefik.http.routers.mydashroot.middlewares= mydashredirectroothttp #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.mydashredirectroothttp.redirectregex.regex=.* #servicebal|          - traefik.http.middlewares.mydashredirectroothttp.redirectregex.regex=.* #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.mydashredirectroothttp.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/dashboard/ #servicebal|          - traefik.http.middlewares.mydashredirectroothttp.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/dashboard/ #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashroothttps.rule=Host(\`${APP_HOST}\`) && (PathPrefix(\`/traefik\`)) #servicebal|          - traefik.http.routers.mydashroothttps.rule=Host(\`${APP_HOST}\`) \&\& (PathPrefix(\`/traefik\`)) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashroothttps.tls=true #servicebal|          - traefik.http.routers.mydashroothttps.tls=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashroothttps.middlewares=mydashredirectroothttp #servicebal|          - traefik.http.routers.mydashroothttps.middlewares=mydashredirectroothttp #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashboard.rule=Host(\`${APP_HOST}\`) && (PathPrefix(\`/dashboard\`) \|\| PathPrefix(\`/api\`)) #servicebal|          - traefik.http.routers.mydashboard.rule=Host(\`${APP_HOST}\`) \&\& (PathPrefix(\`/dashboard\`) \|\| PathPrefix(\`/api\`)) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashboard.tls=true #servicebal|          - traefik.http.routers.mydashboard.tls=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.mydashboard.service=api@internal #servicebal|          - traefik.http.routers.mydashboard.service=api@internal #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.httpmydashboard.rule=Host(\`${APP_HOST}\`) && (PathPrefix(\`/dashboard\`) \|\| PathPrefix(\`/api\`)) #servicebal|          - traefik.http.routers.httpmydashboard.rule=Host(\`${APP_HOST}\`) \&\& (PathPrefix(\`/dashboard\`) \|\| PathPrefix(\`/api\`)) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.httpmydashboard.service=api@internal #servicebal|          - traefik.http.routers.httpmydashboard.service=api@internal #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.myroot.rule=Host(\`${APP_HOST}\`) #servicebal|          - traefik.http.routers.myroot.rule=Host(\`${APP_HOST}\`) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.myroot.middlewares=myrootredirect #servicebal|          - traefik.http.routers.myroot.middlewares=myrootredirect #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.myrootredirect.redirectregex.regex=.* #servicebal|          - traefik.http.middlewares.myrootredirect.redirectregex.regex=.* #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.middlewares.myrootredirect.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|          - traefik.http.middlewares.myrootredirect.redirectregex.replacement=${APP_PROTOCOLO}://${APP_HOST}/sei/ #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.myroothttps.rule=Host(\`${APP_HOST}\`) #servicebal|          - traefik.http.routers.myroothttps.rule=Host(\`${APP_HOST}\`) #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.myroothttps.tls=true #servicebal|          - traefik.http.routers.myroothttps.tls=true #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#          - traefik.http.routers.myroothttps.middlewares=myrootredirect #servicebal|          - traefik.http.routers.myroothttps.middlewares=myrootredirect #servicebal|" orquestrators/docker-compose/docker-compose.yml

	@sed -i'' -e "s|#    volumes_from: #servicebal|    volumes_from: #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - storage-certs #servicebal|        - storage-certs #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#    volumes: #servicebal|    volumes: #servicebal|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#        - /var/run/docker.sock:/var/run/docker.sock #servicebal|        - /var/run/docker.sock:/var/run/docker.sock #servicebal|g" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|nada|nada|" orquestrators/docker-compose/docker-compose.yml

ifeq ("$(APP_PROTOCOLO)",  "https")
	@sed -i'' -e "s|#traefik.http.routers.app.rule: Host(\`${APP_HOST}\`) && (PathPrefix(\`/sei\`) \|\| PathPrefix(\`/sip\`) \|\| PathPrefix(\`/infra\`)) #app-traefik|traefik.http.routers.app.rule: Host(\`${APP_HOST}\`) \&\& (PathPrefix(\`/sei\`) \|\| PathPrefix(\`/sip\`) \|\| PathPrefix(\`/infra\`)) #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.routers.app.entrypoints: websecure #app-traefik|traefik.http.routers.app.entrypoints: websecure #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.routers.app.tls: true #app-traefik|traefik.http.routers.app.tls: true #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.services.app.loadbalancer.server.scheme: https #app-traefik|traefik.http.services.app.loadbalancer.server.scheme: https #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.services.app.loadbalancer.server.port: 443 #app-traefik|traefik.http.services.app.loadbalancer.server.port: 443 #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.services.app.loadBalancer.sticky.cookie: true #app-traefik|traefik.http.services.app.loadBalancer.sticky.cookie: true #app-traefik|" orquestrators/docker-compose/docker-compose.yml

else
	@sed -i'' -e "s|#traefik.http.routers.httpapp.rule: Host(\`${APP_HOST}\`) && (PathPrefix(\`/sei\`) \|\| PathPrefix(\`/sip\`) \|\| PathPrefix(\`/infra\`)) #app-traefik|traefik.http.routers.httpapp.rule: Host(\`${APP_HOST}\`) \&\& (PathPrefix(\`/sei\`) \|\| PathPrefix(\`/sip\`) \|\| PathPrefix(\`/infra\`)) #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.routers.httpapp.entrypoints: web #app-traefik|traefik.http.routers.httpapp.entrypoints: web #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.services.httpapp.loadbalancer.server.scheme: http #app-traefik|traefik.http.services.httpapp.loadbalancer.server.scheme: http #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.services.httpapp.loadbalancer.server.port: 80 #app-traefik|traefik.http.services.httpapp.loadbalancer.server.port: 80 #app-traefik|" orquestrators/docker-compose/docker-compose.yml
	@sed -i'' -e "s|#traefik.http.services.httpapp.loadBalancer.sticky.cookie: true #app-traefik|traefik.http.services.httpapp.loadBalancer.sticky.cookie: true #app-traefik|" orquestrators/docker-compose/docker-compose.yml
endif



ifeq ("$(DBADMIN_PRESENTE)",  "true")
	@sed -i'' -e "s|#        - dbadmin #servicedbadmin #servicebal|        - dbadmin #servicedbadmin #servicebal|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(MEMCACHEDADMIN_PRESENTE)",  "true")
	@sed -i'' -e "s|#        - memcachedadmin #servicememcachedadmin #servicebal|        - memcachedadmin #servicememcachedadmin #servicebal|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(MAIL_CATCHER_PRESENTE)",  "true")
	@sed -i'' -e "s|#        - mail #servicemail #servicebal|        - mail #servicemail #servicebal|" orquestrators/docker-compose/docker-compose.yml
endif

ifeq ("$(OPENLDAP_PRESENTE)",  "true")
	@sed -i'' -e "s|#        - ldapadmin #serviceldap #servicebal|        - ldapadmin #serviceldap #servicebal|" orquestrators/docker-compose/docker-compose.yml
endif



ifeq ("$(BALANCEADOR_PORTA_80_MAP_EXPOR)",  "true")
	@sed -i'' -e "s|#    ports: #servicebal|    ports: #servicebal|" orquestrators/docker-compose/docker-compose.yml
ifneq ("$(BALANCEADOR_PORTA_80_MAP_EXPOR)",  "")
	@sed -i'' -e "s|#        - $(BALANCEADOR_PORTA_80_MAP) #servicebal|        - $(BALANCEADOR_PORTA_80_MAP) #servicebal|" orquestrators/docker-compose/docker-compose.yml
else
    @sed -i'' -e "|#        - $(BALANCEADOR_PORTA_80_MAP) #servicebal|d" orquestrators/docker-compose/docker-compose.yml
endif
endif

ifeq ("$(BALANCEADOR_PORTA_443_MAP_EXPOR)",  "true")
	@sed -i'' -e "s|#    ports: #servicebal|    ports: #servicebal|" orquestrators/docker-compose/docker-compose.yml
ifneq ("$(BALANCEADOR_PORTA_443_MAP_EXPOR)",  "")
	@sed -i'' -e "s|#        - $(BALANCEADOR_PORTA_443_MAP) #servicebal|        - $(BALANCEADOR_PORTA_443_MAP) #servicebal|" orquestrators/docker-compose/docker-compose.yml
else
    @sed -i'' -e "|#        - $(BALANCEADOR_PORTA_443_MAP) #servicebal|d" orquestrators/docker-compose/docker-compose.yml
endif
endif


endif



run: ## roda na sequencia build_docker_compose e up -d

ifeq ("$(EXISTE_LOCAL_DB)",  "")
	@echo "Nao existe volume para o banco, rode antes o comando make criar_volume_banco ou make criar_volumes"

else ifeq ("$(EXISTE_LOCAL_FONTES)",  "")
	@echo "Nao existe volume para os fontes, rode antes o comando make criar_volume_fontes - Verifique antes a variavel LOCALIZACAO_FONTES_SEI no seu env-local.env"
	@echo "A mesma tem que apontar para o diretorio de fontes do SEI (infra, sei, sip)"

else ifeq ("$(EXISTE_LOCAL_ARQUIVOS_EXTERNOS)",  "")
	@echo "Nao existe volume para os arquivos externos, rode antes o comando make criar_volume_arquivos_externos ou make criar_volumes"

else ifeq ("$(EXISTE_LOCAL_SOLR)",  "")
	@echo "Nao existe volume para o SOLR, rode antes o comando make criar_volume_solr ou make criar_volumes"

else ifeq ("$(EXISTE_LOCAL_CONTROLADOR_INSTALACAO)",  "")
	@echo "Nao existe volume para o Controlador de Instalacao, rode antes o comando make criar_volume_controlador_instalacao ou make criar_volumes"

else ifeq ("$(EXISTE_LOCAL_CERTS)",  "")
	@echo "Nao existe volume para os Certificados, rode antes o comando make criar_volume_certs ou make criar_volumes"

else ifeq ("$(EXISTE_LOCAL_OPENLDAP_SLAPD)",  "")
	@echo "Nao existe volume para os OpenldapSlapd, rode antes o comando make criar_volume_openldap_slapd ou make criar_volumes"

else ifeq ("$(EXISTE_LOCAL_OPENLDAP_DB)",  "")
	@echo "Nao existe volume para os Openldap Database, rode antes o comando make criar_volume_openldap_db ou make criar_volumes"

else
	make build_docker_compose
	$(CMDCOMPOSE) up -d --remove-orphans
endif


setup: check-version-compatibility ## executa criar_volumes e run na sequencia
	make criar_volumes
	make run

scale: ## escala os nohs de aplicacao do SEI para 2. Caso vc queira mais de 2 basta usar o comando make qtd=3 scale, substituindo o 3 pelo numero desejado, ou o comando docker-compose scale app=x na pasta orquestrators/docker-compose

	@echo "escala os nohs de aplicacao do SEI para 2. Caso vc queira mais de 2 basta usar o comando make qtd=3 scale, substituindo o 3 pelo numero desejado, ou o comando docker-compose scale app=3 na pasta orquestrators/docker-compose"


ifeq ("$(BALANCEADOR_PRESENTE)",  "true")

ifneq ($(shell docker-compose --help | grep scale 2>/dev/null),)
	$(CMDCOMPOSE) scale app=$(qtd)
else
	$(CMDCOMPOSE) up -d app --scale app=$(qtd) --no-recreate
endif

else
	echo "Scale nao efetuado. Precisa de um balanceador"
endif


stop: ## docker-compose stop e docker-compose rm -f
	$(CMDCOMPOSE) stop
	$(CMDCOMPOSE) rm -f

logs: ## docker-compose logs -f pressione ctrol+c para sair
ifeq ("$(LOGS_SEGUIR)",  "true")
	$(CMDCOMPOSE) logs -f $(LOGS_COMPONENTE)
else
	$(CMDCOMPOSE) logs $(LOGS_COMPONENTE)
endif


logs_app: ## docker-compose logs -f app pressione ctrol+c para sair
	make LOGS_COMPONENTE=app logs

logs_app-atualizador: ## docker-compose logs -f app-atualizador pressione ctrol+c para sair
	make LOGS_COMPONENTE=app-atualizador logs

logs_balanceador: ## docker-compose logs -f balanceador pressione ctrol+c para sair
	make LOGS_COMPONENTE=balanceador logs

logs_openldap: ## docker-compose logs -f openldap pressione ctrol+c para sair
	make LOGS_COMPONENTE=openldap logs

logs_solr: ## docker-compose logs -f solr pressione ctrol+c para sair
	make LOGS_COMPONENTE=solr logs

clear: ## pahra o projeto e remove tds os conteineres, redes criados. Nao remove os volumes
	make stop
	$(CMDCOMPOSE) down -v --remove-orphans

apagar_volumes: ## Apaga todos os volumes do projeto ATENCAO TODOS OS DADOS DE BASE E ARQUIVOS SERAO DELETADOS
	@make apagar_volume_fontes
	@make apagar_volume_certs
	@make apagar_volume_banco
	@make apagar_volume_arquivos_externos
	@make apagar_volume_solr
	@make apagar_volume_openldap
	@make apagar_volume_controlador_instalacao

apagar_volume_generico:
	@v=$$(docker volume ls -q -f "name=$(VOLUME_NAME)"); \
	if [ -z "$$v" ]; then echo "Volume $(VOLUME_NAME) nao encontrado"; \
	else \
	echo "Apagando volume $(VOLUME_NAME)"; \
	docker volume rm $(VOLUME_NAME); fi;


apagar_volume_fontes: ## Apaga o volume docker com os fontes que serao consumidos pelo projeto
	@make VOLUME_NAME=$(VOLUME_FONTES) apagar_volume_generico

apagar_volume_certs: ## Apaga o volume docker com os certs que serao consumidos pelo projeto
	@make VOLUME_NAME=$(VOLUME_CERTS) apagar_volume_generico

apagar_volume_banco: ## Apagar volume do banco
	@make VOLUME_NAME=$(VOLUME_DB) apagar_volume_generico

apagar_volume_arquivos_externos: ## Apagar volume Arquivos Externos
	@make VOLUME_NAME=$(VOLUME_ARQUIVOSEXTERNOS) apagar_volume_generico

apagar_volume_solr: ## Apagar volume Solr
	@make VOLUME_NAME=$(VOLUME_SOLR) apagar_volume_generico

apagar_volume_openldap: ## Apagar volumes OpenLDAP
	@make VOLUME_NAME=$(VOLUME_OPENLDAP_SLAPD) apagar_volume_generico
	@make VOLUME_NAME=$(VOLUME_OPENLDAP_DB) apagar_volume_generico


apagar_volume_controlador_instalacao: ## Apagar volume controlador da instalacao
	@make VOLUME_NAME=$(VOLUME_CONTROLADOR_INSTALACAO) apagar_volume_generico


kubernetes_montar_yaml: ## Montar receitas para kubernetes - apenas mysql
	@echo "Vamos montar os arquivos yaml para o kubernetes. Apenas mysql e somente componentes essenciais..."
	@rm -rf orquestrators/rancher-kubernetes/topublish/configmaps.yaml \
	orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml \
	orquestrators/rancher-kubernetes/topublish/ingress.yaml \
	orquestrators/rancher-kubernetes/topublish/jobs.yaml \
	orquestrators/rancher-kubernetes/topublish/pvc.yaml \
	orquestrators/rancher-kubernetes/topublish/statefullsets.yaml \
	orquestrators/rancher-kubernetes/topublish/secrets.yaml

	@envsubst < orquestrators/rancher-kubernetes/templates/configmaps-template.yaml > orquestrators/rancher-kubernetes/topublish/configmaps.yaml
	@envsubst < orquestrators/rancher-kubernetes/templates/deploys-svc-template.yaml > orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@envsubst < orquestrators/rancher-kubernetes/templates/ingress-template.yaml > orquestrators/rancher-kubernetes/topublish/ingress.yaml
	@envsubst < orquestrators/rancher-kubernetes/templates/jobs-template.yaml > orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@envsubst < orquestrators/rancher-kubernetes/templates/pvc-template.yaml > orquestrators/rancher-kubernetes/topublish/pvc.yaml
	@envsubst < orquestrators/rancher-kubernetes/templates/statefullsets-template.yaml > orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@./generatebase64.sh

ifeq ("$(KUBERNETES_RESOURCES_INFORMAR)",  "true")
	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_APP} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_APP} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_APP} #kb_resources|            cpu: ${KUBERNETES_REQUEST_CPU_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_APP} #kb_resources|        #    memory: ${KUBERNETES_REQUEST_MEMORY_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/jobs.yaml

	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_DB} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_DB} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_DB} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_DB} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_DB} #kb_resources|        #    cpu: ${KUBERNETES_REQUEST_CPU_DB} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_DB} #kb_resources|        #    memory: ${KUBERNETES_REQUEST_MEMORY_DB} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml

	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_SOLR} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_SOLR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_SOLR} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_SOLR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_SOLR} #kb_resources|            cpu: ${KUBERNETES_REQUEST_CPU_SOLR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_SOLR} #kb_resources|            memory: ${KUBERNETES_REQUEST_MEMORY_SOLR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/statefullsets.yaml

	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_MEMCACHED} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_MEMCACHED} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_MEMCACHED} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_MEMCACHED} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_MEMCACHED} #kb_resources|            cpu: ${KUBERNETES_REQUEST_CPU_MEMCACHED} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_MEMCACHED} #kb_resources|            memory: ${KUBERNETES_REQUEST_MEMORY_MEMCACHED} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml

	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_JOD} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_JOD} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_JOD} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_JOD} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_JOD} #kb_resources|            cpu: ${KUBERNETES_REQUEST_CPU_JOD} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_JOD} #kb_resources|            memory: ${KUBERNETES_REQUEST_MEMORY_JOD} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml

	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_APP} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_APP} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_APP} #kb_resources|            cpu: ${KUBERNETES_REQUEST_CPU_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_APP} #kb_resources|            memory: ${KUBERNETES_REQUEST_MEMORY_APP} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml

	@sed -i'' -e "s|        #resources: #kb_resources|        resources: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  limits: #kb_resources|          limits: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_LIMITS_CPU_APP_AGENDADOR} #kb_resources|            cpu: ${KUBERNETES_LIMITS_CPU_APP_AGENDADOR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_LIMITS_MEMORY_APP_AGENDADOR} #kb_resources|            memory: ${KUBERNETES_LIMITS_MEMORY_APP_AGENDADOR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #  requests: #kb_resources|          requests: #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    cpu: ${KUBERNETES_REQUEST_CPU_APP_AGENDADOR} #kb_resources|            cpu: ${KUBERNETES_REQUEST_CPU_APP_AGENDADOR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml
	@sed -i'' -e "s|        #    memory: ${KUBERNETES_REQUEST_MEMORY_APP_AGENDADOR} #kb_resources|            memory: ${KUBERNETES_REQUEST_MEMORY_APP_AGENDADOR} #kb_resources|" orquestrators/rancher-kubernetes/topublish/deploys-svc.yaml

endif

	@echo "Arquivos gerados no diretorio orquestrators/rancher-kubernetes/topublish"
	@echo "Verifique cada um antes de publicar"
	@echo "Como existem muitas variações de versões de kubernetes e orquestradores pode ser necessário algum ajuste adicional"
	@echo "Esses foram testados em um kubernetes 1.21 embaixo de plataforma rancher"
	@echo "Comece publicando o configmap e o secret, depois os pvcs e em seguida os demais componentes"
	@echo "Como o codigo fonte do SEI nao eh publico faz-se necessario mover manualmente os fontes para o pvc vol-sei-fontes"


kubernetes_apply: ## Aplicar receitas no cluster kubernetes
	cd orquestrators/rancher-kubernetes/topublish/; \
	kubectl --insecure-skip-tls-verify apply -f configmaps.yaml; \
	kubectl --insecure-skip-tls-verify apply -f secrets.yaml; \
	kubectl --insecure-skip-tls-verify apply -f pvc.yaml; \
	kubectl --insecure-skip-tls-verify apply -f jobs.yaml; \
	kubectl --insecure-skip-tls-verify apply -f statefullsets.yaml; \
	kubectl --insecure-skip-tls-verify apply -f deploys-svc.yaml; \
	kubectl --insecure-skip-tls-verify apply -f ingress.yaml;


kubernetes_delete: ## deletar receitas do cluster
	cd orquestrators/rancher-kubernetes/topublish/; \
	kubectl --insecure-skip-tls-verify  delete -f ingress.yaml --wait=true --cascade='foreground'; \
	kubectl --insecure-skip-tls-verify  delete -f deploys-svc.yaml --wait=true --cascade='foreground'; \
	kubectl --insecure-skip-tls-verify  delete -f statefullsets.yaml --wait=true --cascade='foreground'; \
	kubectl --insecure-skip-tls-verify  delete -f jobs.yaml --wait=true --cascade='foreground'; \
	kubectl --insecure-skip-tls-verify  delete -f configmaps.yaml --wait=true --cascade='foreground'; \
	kubectl --insecure-skip-tls-verify  delete -f secrets.yaml --wait=true --cascade='foreground'; \
	kubectl --insecure-skip-tls-verify  delete -f pvc.yaml --wait=true --cascade='foreground';


kubernetes_check_deploy_generic: ## verifica se determinado deploy ja esta pronto para uso no cluster
	kubectl --insecure-skip-tls-verify wait deployment $(KUBE_DEPLOY_NAME) --for condition="Available=True" --namespace=$$KUBERNETES_NAMESPACE --timeout=$(kube_timeout)