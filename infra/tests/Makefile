SHELL := /bin/bash

include ../envlocal.env

define MENSAGEM_CONFIRMACAO
Vamos tentar agora subir o SEI de varias formas diferentes para ver se o resultado bate com o esperado.
Tenha em mente que iremos usar diversos recursos.
Verifique se nao ha nada rodando em localhost ou nas urls de teste que possam impactar a subida.
Verifique tb se vc fez backup do seu envlocal.env pois o mesmo sera substituido por referencias padrao

Verifique se os dados estao salvos pois os volumes atuais serao apagados.
Verifique se o codigo fonte esta posicionado no local correto pois o volume de fontes sera reconstruido

Dentre os inumeros testes vamos rodar tb um teste em selenium. Por conta de limitacoes tecnicas o teste
selenium para o caso de ambiente com url localhost sera pulado caso vc nao esteja em linux. As outras urls
serao testadas normalmente

Pressione y para continuar [y/n]...
endef
export MENSAGEM_CONFIRMACAO


DIR := ${CURDIR}
COMMMADCOMPOSE = docker-compose -f orquestrators/docker-compose/docker-compose.yml

CMDMAKE = $(MAKE) -C ../
MENSAGEM = Vamos tentar agora subir o SEI de várias formas diferentes para ver se o resultado bate com o esperado. Tenha em mente que iremos usar diversos recursos. Verifique se nao ha nada rodando em localhost ou nas urls de teste que possam impactar a subida. Pressione y para continuar [y/n]...

ifndef VERSAOSPE
VERSAOSPE:=$(shell echo $$( grep -o "const SEI_VERSAO = '.*';" $(LOCALIZACAO_FONTES_SEI)/sei/web/SEI.php | head -c 21 | tail -c 1 ))
endif
ifndef VERSAOSPE
VERSAOSPE:=$(shell echo $$( grep "'SEI_VERSAO'" $(LOCALIZACAO_FONTES_SEI)/sei/web/SEI.php | head -c 22 | tail -c 1 ))
endif

ifndef BANCO
BANCO = mysql
endif

ifndef CONFIRMACAO
CONFIRMACAO = n
endif

ifndef MODALIDADE
MODALIDADE = default
endif

ifeq ("$(APP_PROTOCOLO)",  "https")
PORTA_URL=443
else
PORTA_URL=80
endif


CURLCMD = curl -s -k -L --resolve "$(APP_HOST):$(PORTA_URL):127.0.0.1" $(APP_PROTOCOLO)://$(APP_HOST)/sei
CURLCMD_SIP = curl -s -k -L --resolve "$(APP_HOST):$(PORTA_URL):127.0.0.1" $(APP_PROTOCOLO)://$(APP_HOST)/sip
CURLCMD_301 = $(CURLCMD) | grep 301
CURLCMDSEI_LOGIN = $(CURLCMD) | grep txtUsuario
CURLCMDSIP_LOGIN = $(CURLCMD_SIP) | grep txtUsuario

CURLTRAEFIK = curl -s -kL --resolve "$(APP_HOST):80:127.0.0.1" --resolve "$(APP_HOST):443:127.0.0.1" http://$(APP_HOST)/traefik | grep "Traefik UI"
CURLTRAEFIKFULL = curl -s -kL  --resolve "$(APP_HOST):80:127.0.0.1" --resolve "$(APP_HOST):443:127.0.0.1" http://$(APP_HOST)/traefik
CURLSOLR = curl -s -k -L --resolve "$(APP_HOST):443:127.0.0.1"  --resolve "$(APP_HOST):80:127.0.0.1"  https://$(APP_HOST)/solr | grep Dashboard
CURLDBADMIN = curl -s -k -L --resolve "$(APP_HOST):80:127.0.0.1" --resolve "$(APP_HOST):443:127.0.0.1" https://$(APP_HOST)/dbadmin | grep Adminer
CURLMEMCACHEDADMIN = curl -s -k -L --resolve "$(APP_HOST):80:127.0.0.1" --resolve "$(APP_HOST):443:127.0.0.1" https://$(APP_HOST)/memcachedadmin/ | grep phpMemcachedAdmin
CURLPHPLDAPADMIN = curl -s -k -L --resolve "$(APP_HOST):80:127.0.0.1" --resolve "$(APP_HOST):443:127.0.0.1" https://$(APP_HOST)/phpldapadmin/ | grep phpLDAPadmin
CURLMAILCATCHER = curl -s -k -L --resolve "$(APP_HOST):80:127.0.0.1" --resolve "$(APP_HOST):443:127.0.0.1" https://$(APP_HOST)/mailadmin | grep MailCatcher


ifndef COMPONENTE_PRESENCA
COMPONENTE_PRESENCA = jod
COMPONENTE_PRESENCA_VAR = JOD_PRESENTE
endif


ifeq ("$(BALANCEADOR_PRESENTE)",  "true")
LINK_SEI=balanceador
else
LINK_SEI=app
endif

ifndef TESTE_SELENIUM_USER_SENHA
TESTE_SELENIUM_USER_SENHA=teste
endif

ISLINUX:=$(shell echo $$(uname | grep -i linux | wc -l))
CMDSELENIUMCHROME=docker run -d --rm --name seleniumchrome  -p 4444:4444 --network=docker-compose_default --link $(LINK_SEI):$(APP_HOST) -v /dev/shm:/dev/shm selenium/standalone-chrome:4.0.0-rc-1-prerelease-20210618
CMDSELENIUMCHROMELOCAL=docker run -d --rm --name seleniumchrome  -p 4444:4444 --network=host -v /dev/shm:/dev/shm selenium/standalone-chrome:4.0.0-rc-1-prerelease-20210618

ifeq ("$(APP_HOST)",  "localhost")
CMDSELENIUMTEST=docker run --rm -it -v "$$PWD"/Selenium/PythonExported:/t -w /t --network=host linhares/pytestseleniumdocker:latest bash -c "echo '127.0.0.1 seleniumchrome' >> /etc/hosts && pytest --disable-pytest-warnings -W ignore::DeprecationWarning -o junit_family=xunit2 --junitxml=/t/resultado.xml test_suiteBasics-executar.py"
ifeq ("$(ISLINUX)",  "1")

endif

else
	CMDSELENIUMTEST=docker run --rm -it -v "$$PWD"/Selenium/PythonExported:/t -w /t --network=docker-compose_default --link seleniumchrome linhares/pytestseleniumdocker:latest bash -c "pytest --disable-pytest-warnings -W ignore::DeprecationWarning -o junit_family=xunit2 --junitxml=/t/resultado.xml test_suiteBasics-executar.py"

endif


help: ## Mostra essa ajuda. Voce pode usar tab para completar os comandos
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | sed -e 's|^\ ||'


test_confirmation: ## target de apoio mensagem de confirmacao
ifeq ("$(CONFIRMACAO)",  "n")
	@( read -p "$$MENSAGEM_CONFIRMACAO" sure && case "$$sure" in [yY]) true;; *) false;; esac )
else
	@echo "---"
endif


## test_lineup_completa: RODE ESSE TARGET PARA RODAR TODA A STACK DE TESTES PARA A PASTA INFRA
test_lineup_completa: test_confirmation
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos iniciar uma execucao da montagem do ambiente reduzida com url customizada, nas 4 bases de dados. "
	@echo "Em seguida vamos iniciar uma execucao da montagem do ambiente default com localhost, nas 4 bases de dados. "
	@echo "Em seguida vamos iniciar uma execucao da montagem do ambiente completa com url customizada, nas 4 bases de dados."
	@echo "Em seguida vamos iniciar uma execucao da montagem do ambiente em http, nas 4 bases de dados. Aguarde ... "
	@echo "*****************************************************"
	@echo "*****************************************************"
	@sleep 5

	make CONFIRMACAO=y test_ambientes_reduzida
	make CONFIRMACAO=y test_ambientes_default
	make CONFIRMACAO=y test_ambientes_full
	make CONFIRMACAO=y  test_ambientes_http

	cp -f ../envlocal-example-mysql-sei4.env ../envlocal.env

	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Testes Finalizados."
	@echo "*****************************************************"
	@echo "*****************************************************"


## test_ambientes_default: Roda o teste subindo ambiente default em localhost nas 4 bases
test_ambientes_default: test_confirmation
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos iniciar uma execucao da montagem do ambiente default, nas 4 bases de dados. Aguarde..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@sleep 5

	make CONFIRMACAO=y BANCO=mysql test_ambiente
	make CONFIRMACAO=y BANCO=sqlserver test_ambiente
	make CONFIRMACAO=y BANCO=oracle test_ambiente
	make CONFIRMACAO=y BANCO=postgres test_ambiente


## test_ambientes_reduzida: Roda o teste subindo ambiente reduzido com url custom, nas 4 bases de dados
test_ambientes_reduzida: test_confirmation
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos iniciar uma execucao da montagem do ambiente reduzida com url customizada, nas 4 bases de dados. Aguarde..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@sleep 5

	make MODALIDADE=reduzida CONFIRMACAO=y BANCO=mysql test_ambiente
	make MODALIDADE=reduzida CONFIRMACAO=y BANCO=sqlserver test_ambiente
	make MODALIDADE=reduzida CONFIRMACAO=y BANCO=oracle test_ambiente
	make MODALIDADE=reduzida CONFIRMACAO=y BANCO=postgres test_ambiente


## test_ambientes_http: Roda o teste subindo ambiente com http com url custom, nas 4 bases de dados
test_ambientes_http: test_confirmation
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos iniciar uma execucao da montagem dos ambientes com http no sei ao inves de https. Aguarde..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@sleep 5

	make MODALIDADE=reduzida-http CONFIRMACAO=y BANCO=mysql test_ambiente
	make MODALIDADE=reduzida-http CONFIRMACAO=y BANCO=sqlserver test_ambiente
	make MODALIDADE=reduzida-http CONFIRMACAO=y BANCO=oracle test_ambiente
	make MODALIDADE=reduzida-http CONFIRMACAO=y BANCO=postgres test_ambiente

	make MODALIDADE=default-http CONFIRMACAO=y BANCO=mysql test_ambiente


## test_ambientes_full: Roda o teste subindo ambiente full com todos os componentes com url custom, nas 4 bases de dados
test_ambientes_full: test_confirmation
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos iniciar uma execucao da montagem do ambiente completa com url customizada, nas 4 bases de dados. Aguarde..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@sleep 5

	make MODALIDADE=completa CONFIRMACAO=y BANCO=mysql TESTE_SELENIUM_USER_SENHA=123456 test_ambiente
	make MODALIDADE=completa CONFIRMACAO=y BANCO=sqlserver TESTE_SELENIUM_USER_SENHA=123456 test_ambiente
	make MODALIDADE=completa CONFIRMACAO=y BANCO=oracle TESTE_SELENIUM_USER_SENHA=123456 test_ambiente
	make MODALIDADE=completa CONFIRMACAO=y BANCO=postgres TESTE_SELENIUM_USER_SENHA=123456 test_ambiente


## test_ambiente: Roda o teste em um unico ambiente. Deve ser passado parametro para Base e Modalidade
test_ambiente: test_confirmation

	@echo "" ; echo "" ; echo "";
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Modalidade dessa Execucao... $(MODALIDADE) ..."
	@echo "Banco de Dados dessa Execucao... $(BANCO) ..."
	@echo "Versao do SPE... $(VERSAOSPE) ..."	
	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos desligar o projeto atual, caso esteja rodando, e apagar os volumes... Aguarde..."
	@$(CMDMAKE) -s build_docker_compose
	@$(CMDMAKE) -s clear
	@$(CMDMAKE) -s apagar_volumes

	make -s test_verificar_volumes_nao_existe

	@echo "Vamos copiar o envlocal de referencia para o $(BANCO)-sei$(VERSAOSPE)..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	cp -f ../envlocal-example-mysql-sei4.env ../envlocal.env
ifeq ("$(MODALIDADE)",  "default")
	cat ../envlocal-example-$(BANCO)-sei$(VERSAOSPE).env >> ../envlocal.env
else
	cat ../envlocal-example-$(BANCO)-sei$(VERSAOSPE).env >> ../envlocal.env
	cat test-envlocal-$(MODALIDADE).env >> ../envlocal.env
endif


	@echo "Vamos subir o projeto..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@$(CMDMAKE) -s setup

	@echo "Vamos verificar os volumes..."
	@echo "*****************************************************"
	@echo "*****************************************************"

	make -s test_verificar_volumes_existe

	make -s test_verificar_app_url

	make -s test_verficar_componentes

	make -s test_verificar_balanceador_urls

	make -s test_escalonar

	make -s test_selenium_basico1

	@echo "Vamos destruir o sistema..."
	@echo "*****************************************************"
	@echo "*****************************************************"

	$(CMDMAKE) -s clear
	$(CMDMAKE) -s apagar_volumes

	make -s test_verificar_volumes_nao_existe

	@echo "*****************************************************"
	@echo "***VERIFICACAO DE TESTE DO $(BANCO) FINALIZADA**********"
	@echo "*****************************************************"



test_verificar_componente_presenca: ## target de apoio verifica componente presente via parametro
	@echo "Vamos verificar se criou com ou sem o $(COMPONENTE_PRESENCA)"
	@echo "*****************************************************"
	@echo "*****************************************************"

	@var=$$(echo $$(docker ps | grep $(COMPONENTE_PRESENCA))) && \
	if [ "$${$(COMPONENTE_PRESENCA_VAR)}" = "true" ]; then \
	if [ "$$var" = "" ]; then echo '$(COMPONENTE_PRESENCA) nao encontrado. Deveria ter sido encontrado. Saindo do teste'; exit 1; fi; \
	else \
	if [ "$$var" != "" ]; then echo '$(COMPONENTE_PRESENCA) encontrado. Nao deveria ter sido encontrado. Saindo do teste'; exit 1; fi; \
	fi


	@echo "Verificacao de presenca ou nao do $(COMPONENTE_PRESENCA) com sucesso"
	@echo "*****************************************************"
	@echo "*****************************************************"


## test_verficar_componentes: target de apoio Verifica se os componentes JOD, balanceador, dbadmin, memcachedadmin, mailcatcher e openldap devem ou nao estar presentes
test_verficar_componentes: test_verificar_jod_presenca test_verificar_balanceador_presenca test_verificar_dbadmin_presenca test_verificar_memcachedadmin_presenca test_verificar_mailcatcher_presenca test_verificar_openldap_presenca


test_verificar_jod_presenca: ## target de apoio verifica se o jod esta presente
	make test_verificar_componente_presenca


test_verificar_balanceador_presenca: ## target de apoio verificar se o balanceador esta presente
	make COMPONENTE_PRESENCA=balanceador COMPONENTE_PRESENCA_VAR=BALANCEADOR_PRESENTE test_verificar_componente_presenca


test_verificar_dbadmin_presenca: ## target de apoio verifica se o dbadmin esta presente
	make COMPONENTE_PRESENCA=dbadmin COMPONENTE_PRESENCA_VAR=DBADMIN_PRESENTE test_verificar_componente_presenca


test_verificar_memcachedadmin_presenca: ## target de apoio verifica se o memcached esta presente
	make COMPONENTE_PRESENCA=memcachedadmin COMPONENTE_PRESENCA_VAR=MEMCACHEDADMIN_PRESENTE test_verificar_componente_presenca


test_verificar_mailcatcher_presenca: ## target de apoio verifica se o mailcatcher esta presente
	make COMPONENTE_PRESENCA=mailcatcher COMPONENTE_PRESENCA_VAR=MAIL_CATCHER_PRESENTE test_verificar_componente_presenca


test_verificar_openldap_presenca: ## target de apoio verifica se o openldap esta presente
	make COMPONENTE_PRESENCA=openldap COMPONENTE_PRESENCA_VAR=OPENLDAP_PRESENTE test_verificar_componente_presenca



test_verificar_app_url: ## target de apoio verifica se a url do sei responde a pagina de login

	@echo "Vamos verificar se o SEI responde a pagina inicial"
	@echo "*****************************************************"
	@echo "*****************************************************"

	@echo "Tentando acessar o login do SEI"
	@echo "*****************************************************"
	@echo "*****************************************************";

	@for i in {1..15}; do \
		echo 'Tentando acessar...'; \
		var=$$(echo $$($(CURLCMDSEI_LOGIN))); \
		if [ "$$var" != "" ]; then \
			echo 'Pagina respondeu com tela de login'; \
			break; \
		else \
			echo 'Aguardando resposta ...'; \
		fi; \
		sleep 10; \
	done
	@var=$$(echo $$($(CURLCMDSEI_LOGIN)));  if [ "$$var" = "" ]; then echo 'Nao respondeu tela de login. Saindo do teste'; exit 1; fi

	@echo "Tentando acessar o login do SIP"
	@echo "*****************************************************"
	@echo "*****************************************************"
	@for i in {1..15}; do echo 'Tentando acessar...'; var=$$(echo $$($(CURLCMDSIP_LOGIN))); if [ "$$var" != "" ]; then echo 'Pagina respondeu com tela de login'; break; else echo 'Aguardando resposta ...'; fi; sleep 10; done
	@var=$$(echo $$($(CURLCMDSIP_LOGIN)));  if [ "$$var" = "" ]; then echo 'Nao respondeu tela de login. Saindo do teste'; exit 1; fi

	@echo "SEI respondeu a pagina inicial"
	@echo "*****************************************************"
	@echo "*****************************************************"


test_verificar_balanceador_urls: ## target de apoio verifica se as urls fornecidas no balanceador estao ativas e funcionando
	@echo "Caso haja um balanceador vamos averiguar os seus backends..."

ifeq ("$(BALANCEADOR_PRESENTE)",  "true")

	@for i in {1..3}; do echo 'Tentando acessar traefik...'; var=$$(echo $$($(CURLTRAEFIK))); if [ "$$var" != "" ]; then echo 'Pagina traefik respondeu com sucesso'; break; else echo 'Aguardando resposta traefik ...'; fi; sleep 10;  done
	@var=$$(echo $$($(CURLTRAEFIK)));  if [ "$$var" = "" ]; then echo 'Traefik nao respondeu tela inicial. Saindo do teste'; exit 1; fi

	@for i in {1..3}; do echo 'Tentando acessar solr admin...'; var=$$(echo $$($(CURLSOLR))); if [ "$$var" != "" ]; then echo 'Pagina solr respondeu com sucesso'; break; else echo 'Aguardando resposta solr ...'; fi; sleep 10;  done
	@var=$$(echo $$($(CURLSOLR)));  if [ "$$var" = "" ]; then echo 'SOLR nao respondeu tela inicial. Saindo do teste'; exit 1; fi

ifeq ("$(DBADMIN_PRESENTE)",  "true")
	@for i in {1..3}; do echo 'Tentando acessar dbadmin...'; var=$$(echo $$($(CURLDBADMIN))); if [ "$$var" != "" ]; then echo 'Pagina Admner respondeu com sucesso'; break; else echo 'Aguardando resposta adminer ...'; fi; sleep 10;  done
	@var=$$(echo $$($(CURLDBADMIN)));  if [ "$$var" = "" ]; then echo 'Adminer nao respondeu tela inicial. Saindo do teste'; exit 1; fi
endif

ifeq ("$(MEMCACHEDADMIN_PRESENTE)",  "true")
	@for i in {1..3}; do echo 'Tentando acessar memcachedadmin...'; var=$$(echo $$($(CURLMEMCACHEDADMIN))); if [ "$$var" != "" ]; then echo 'Pagina Memcachedadmin respondeu com sucesso'; break; else echo 'Aguardando resposta Memcachedadmin ...'; fi; sleep 10;  done
	@var=$$(echo $$($(CURLMEMCACHEDADMIN)));  if [ "$$var" = "" ]; then echo 'Memcachedadmin nao respondeu tela inicial. Saindo do teste'; exit 1; fi
endif

ifeq ("$(OPENLDAP_PRESENTE)",  "true")
	@for i in {1..3}; do echo 'Tentando acessar PHPLDAPADMIN...'; var=$$(echo $$($(CURLPHPLDAPADMIN))); if [ "$$var" != "" ]; then echo 'Pagina PHPLDAPADMIN respondeu com sucesso'; break; else echo 'Aguardando resposta PHPLDAPADMIN ...'; fi; sleep 10;  done
	@var=$$(echo $$($(CURLPHPLDAPADMIN)));  if [ "$$var" = "" ]; then echo 'PHPLDAPADMIN nao respondeu tela inicial. Saindo do teste'; exit 1; fi
endif

ifeq ("$(MAIL_CATCHER_PRESENTE)",  "true")
	@for i in {1..3}; do echo 'Tentando acessar MAILCATCHER...'; var=$$(echo $$($(CURLMAILCATCHER))); if [ "$$var" != "" ]; then echo 'Pagina MAILCATCHER respondeu com sucesso'; break; else echo 'Aguardando resposta MAILCATCHER ...'; fi; sleep 10;  done
	@var=$$(echo $$($(CURLMAILCATCHER)));  if [ "$$var" = "" ]; then echo 'MAILCATCHER nao respondeu tela inicial. Saindo do teste'; exit 1; fi
endif

else

	@echo "Balanceador nao encontrado..."

endif


test_escalonar: ## target de apoio escalona o app para 2 para 1 depois para 3 e volta para 1
ifeq ("$(BALANCEADOR_PRESENTE)",  "true")
	@echo "Vamos escalonar a aplicacao para verificar o comportamento"
	@echo "*****************************************************"
	@echo "*****************************************************"

	@echo "Escalonando para 2 instancias e verificando..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@$(CMDMAKE) scale
	@var=$$(echo $$(docker ps | grep -E '(app-1|app_1)')) \
	&& \
	if [ "$$var" = "" ]; then \
	echo 'App1 nao encontrado. Saindo do teste'; exit 1; \
	else \
	echo 'App1 encontrado com sucesso'; \
	fi; \
	\
	var=$$(echo $$(docker ps | grep -E '(app-2|app_2)')); \
	if [ "$$var" = "" ]; then \
	echo 'App2 nao encontrado. Saindo do teste'; exit 1; \
	else \
	echo 'App2 encontrado com sucesso'; \
	fi; \
	\
	for i in {1..6}; do echo 'Tentando acessar logs do noh 2...'; var=$$(echo $$($(CMDMAKE) LOGS_SEGUIR=false logs_app | grep -E '(app-2|app_2)' | grep "Apache Liberado")); if [ "$$var" = "" ]; then echo 'Aguardando noh2 responder...'; else echo 'Noh 2 respondeu com sucesso'; break; fi; sleep 5;  done; \
	var=$$(echo $$($(CMDMAKE) LOGS_SEGUIR=false logs_app | grep -E '(app-2|app_2)' | grep "Apache Liberado"));  if [ "$$var" = "" ]; then echo 'Noh2 nao respondeu como esperado. Saindo do teste...'; exit 1; fi; \
	\
	for i in {1..6}; do echo 'Tentando acessar traefik...'; var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI")); if [ "$$var" != "" ]; then echo 'Pagina traefik respondeu com sucesso balanceamento'; break; else echo 'Aguardando resposta traefik ...'; fi; sleep 5;  done; \
	var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI"));  if [ "$$var" = "" ]; then echo 'Traefik nao respondeu com balanceamento esperado. Saindo do teste'; exit 1; fi;

	@echo "Escalonando de volta para 1 e verificando..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@$(CMDMAKE) qtd=1 scale && sleep 5
	@var=$$(echo $$(docker ps | grep "app-ci:\|app-ci-php8:" | grep Up | grep -v atualizador | wc -l | xargs)) \
	&& \
	if [ "$$var" = "1" ]; then \
	echo 'Unico app encontrado.'; \
	else \
	echo 'Numero de apps diferente do esperado. Saindo do teste'; exit 1; \
	fi; \
	\
	for i in {1..6}; do echo 'Tentando acessar traefik...'; var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI")); if [ "$$var" != "" ]; then echo 'Pagina traefik respondeu com sucesso balanceamento'; break; else echo 'Aguardando resposta traefik ...'; fi; sleep 5;  done; \
	var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI"));  if [ "$$var" = "" ]; then echo 'Traefik nao respondeu com balanceamento esperado. Saindo do teste'; exit 1; fi; \


	@echo "Escalonando para 3 instancias e verificando..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@$(CMDMAKE) scale && sleep 10
	@$(CMDMAKE) qtd=3 scale
	@var=$$(echo $$(docker ps | grep "app-ci:\|app-ci-php8:" | grep Up | grep -v atualizador | wc -l | xargs)) \
	&& \
	if [ ! "$$var" = "3" ]; then \
	echo 'Apps nao disponiveis. Saindo do teste'; exit 1; \
	else \
	echo 'Apps disponiveis com sucesso'; \
	fi; \
	for i in {1..6}; do echo 'Tentando acessar traefik...'; var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI")); if [ "$$var" != "" ]; then echo 'Pagina traefik respondeu com sucesso balanceamento'; break; else echo 'Aguardando resposta traefik ...'; fi; sleep 5;  done; \
	var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI"));  if [ "$$var" = "" ]; then echo 'Traefik nao respondeu com balanceamento esperado. Saindo do teste'; exit 1; fi; \
	sleep 1 \
	&& \
	for i in {1..6}; do echo 'Tentando acessar traefik...'; var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI")); if [ "$$var" != "" ]; then echo 'Pagina traefik respondeu com sucesso balanceamento'; break; else echo 'Aguardando resposta traefik ...'; fi; sleep 5;  done; \
	var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI"));  if [ "$$var" = "" ]; then echo 'Traefik nao respondeu com balanceamento esperado. Saindo do teste'; exit 1; fi;

	@echo "Escalonando de volta para 1 e verificando..."
	@echo "*****************************************************"
	@echo "*****************************************************"
	@$(CMDMAKE) qtd=1 scale && sleep 5
	@var=$$(echo $$(docker ps | grep "app-ci:\|app-ci-php8:" | grep Up | grep -v atualizador | wc -l | xargs)) \
	&& \
	if [ "$$var" = "1" ]; then \
	echo 'Unico app encontrado.'; \
	else \
	echo 'Numero de apps diferente do esperado. Saindo do teste'; exit 1; \
	fi; \
	\
	for i in {1..6}; do echo 'Tentando acessar traefik...'; var=$$(echo $$($(CURLTRAEFIKFULL) | grep app)); if [ "$$var" != "" ]; then echo 'Pagina traefik respondeu com sucesso balanceamento'; break; else echo 'Aguardando resposta traefik ...'; fi; sleep 5;  done; \
	var=$$(echo $$($(CURLTRAEFIKFULL) | grep "Traefik UI"));  if [ "$$var" = "" ]; then echo 'Traefik nao respondeu com balanceamento esperado. Saindo do teste'; exit 1; fi;

else

	@echo "Balanceador nao setado portanto nao sera feito teste de escalonamento"
	@echo "*****************************************************"
	@echo "*****************************************************"

endif

test_selenium_basico1: ## target de apoio roda um teste em selenium apontando para a instancia escolhida para criar processo com anexos

	@echo "*****************************************************"
	@echo "*****************************************************"
	@echo "Vamos agora rodar um teste simples no Selenium para saber se estao de acordo:"
	@echo "- login;"
	@echo "- modulos basicos instalados;"
	@echo "- criacao de processo com anexo e doc interno;"
	@echo "- logout"
	@echo "*****************************************************"
	@echo "*****************************************************"

	@echo "Subindo SeleniumChrome..."

	@var=$$(echo $$(docker ps -a | grep "seleniumchrome"));  if [ "$$var" != "" ]; then docker stop seleniumchrome; fi;

ifeq ("$(APP_HOST)",  "localhost")

ifeq ("$(ISLINUX)",  "1")
	@echo "Subindo SeleniumChrome na rede do host"
	@$(CMDSELENIUMCHROMELOCAL)
else
	@echo "Ambiente subiu com url localhost, pulando testes Selenium para quem nao usa linux. Recomendavel rodar esse teste em linux."
endif

else
	@echo "Subindo SeleniumChrome na rede docker"
	@$(CMDSELENIUMCHROME)

endif


ifneq ($(APP_HOST)$(ISLINUX),  localhost0)

	@for i in {1..4}; do echo 'Tentando acessar SeleniumChrome...'; var=$$(echo $$(docker logs  seleniumchrome | grep "Started Selenium Standalone")); if [ "$$var" != "" ]; then echo 'SeleniumChrome respondeu com sucesso....'; break; else echo 'Aguardando SeleniumChrome...'; fi; sleep 5;  done; \
	var=$$(echo $$(docker logs  seleniumchrome | grep "Started Selenium Standalone"));  if [ "$$var" = "" ]; then echo 'Selenium nao subiu. Saindo do teste'; exit 1; fi;

	@echo "Selenium no ar vamos rodar o teste...";
	@rm -f Selenium/PythonExported/test_suiteBasics-executar.py && cp Selenium/PythonExported/test_suiteBasics.py Selenium/PythonExported/test_suiteBasics-executar.py
	@sed -i'' -e "s|<<PROTOCOLO>>|$(APP_PROTOCOLO)|" Selenium/PythonExported/test_suiteBasics-executar.py
	@sed -i'' -e "s|<<HOST>>|$(APP_HOST)|" Selenium/PythonExported/test_suiteBasics-executar.py
	@sed -i'' -e "s|<<SENHA>>|$(TESTE_SELENIUM_USER_SENHA)|" Selenium/PythonExported/test_suiteBasics-executar.py

ifeq ("$(APP_HOST)",  "localhost")

ifeq ("$(ISLINUX)",  "1")
	@echo "Subindo teste na rede do host"
endif

else
	@echo "Subindo teste na rede docker"

endif

	@$(CMDSELENIUMTEST) || true

	@echo ""; \
	var=$$(grep "failure message" Selenium/PythonExported/resultado.xml); \
	if [ "$$var" != "" ]; then echo 'Deu erro no teste Selenium, vamos tentar novamente em 20segs'; sleep 20; $(CMDSELENIUMTEST) || true; fi;

	@echo ""; \
	var=$$(grep "failure message" Selenium/PythonExported/resultado.xml); \
	if [ "$$var" != "" ]; then echo 'Deu erro novamente no teste Selenium, vamos tentar outra em 20segs'; sleep 20; $(CMDSELENIUMTEST) || true; fi;

	@echo ""
	@echo "Testes finalizados"
	@echo "Obrigado SeleniumChrome, mas vou te destruir..."
	@docker stop seleniumchrome


	@echo ""; \
	var=$$(grep "failure message" Selenium/PythonExported/resultado.xml); \
	if [ "$$var" != "" ]; then echo 'Deu erro no teste Selenium, verifique. Parando o restante dos testes...'; exit 1; fi;

	@echo ""; \
	var=$$(grep "test setup failure" Selenium/PythonExported/resultado.xml); \
	if [ "$$var" != "" ]; then echo 'Deu erro no teste Selenium, verifique. Parando o restante dos testes...'; exit 1; fi;
endif



test_verificar_volumes_existe: ## target de apoio Verifica volumes existentes
	@echo "Vamos verificar se os volumes existem"
	@echo "*****************************************************"
	@echo "*****************************************************"
	@var=$$(echo $$(docker volume ls | grep arquivosexternos-storage)) && if [ "$$var" = "" ]; then echo 'Volume arquivosexternos nao encontrado. Saindo do teste'; exit 1; else echo 'Volume arquivosexternos encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep certs-storage)) && if [ "$$var" = "" ]; then echo 'Volume certs nao encontrado. Saindo do teste'; exit 1; else echo 'Volume certs encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep controlador-instalacao-storage)) && if [ "$$var" = "" ]; then echo 'Volume controlador-instalacao-storage nao encontrado. Saindo do teste'; exit 1; else echo 'Volume controlador-instalacao-storage encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep fontes-storage)) && if [ "$$var" = "" ]; then echo 'Volume fontes-storage nao encontrado. Saindo do teste'; exit 1; else echo 'Volume fontes-storage encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep openldap-db-storage)) && if [ "$$var" = "" ]; then echo 'Volume openldap-db-storage nao encontrado. Saindo do teste'; exit 1; else echo 'Volume openldap-db-storage encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep openldap-slapd-storage)) && if [ "$$var" = "" ]; then echo 'Volume openldap-slapd-storage nao encontrado. Saindo do teste'; exit 1; else echo 'Volume openldap-slapd-storage encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep storage-db)) && if [ "$$var" = "" ]; then echo 'Volume storage-db nao encontrado. Saindo do teste'; exit 1; else echo 'Volume storage-db encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep volume-solr)) && if [ "$$var" = "" ]; then echo 'Volume volume-solr nao encontrado. Saindo do teste'; exit 1; else echo 'Volume volume-solr encontrado'; fi

	@echo "Verificacao de volumes realizada com sucesso"
	@echo "*****************************************************"
	@echo "*****************************************************"


test_verificar_volumes_nao_existe: ## target de apoio Verifica se os volumes nao existem indicando a delecao
	@echo "Vamos verificar se os volumes nao existem"
	@echo "*****************************************************"
	@echo "*****************************************************"
	@var=$$(echo $$(docker volume ls | grep arquivosexternos-storage)) && if [ "$$var" != "" ]; then echo 'Volume arquivosexternos encontrado. Saindo do teste'; exit 1; else echo 'Volume arquivosexternos nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep certs-storage)) && if [ "$$var" != "" ]; then echo 'Volume certs encontrado. Saindo do teste'; exit 1; else echo 'Volume certs nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep controlador-instalacao-storage)) && if [ "$$var" != "" ]; then echo 'Volume controlador-instalacao-storage encontrado. Saindo do teste'; exit 1; else echo 'Volume controlador-instalacao-storage nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep fontes-storage)) && if [ "$$var" != "" ]; then echo 'Volume fontes-storage encontrado. Saindo do teste'; exit 1; else echo 'Volume fontes-storage nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep openldap-db-storage)) && if [ "$$var" != "" ]; then echo 'Volume openldap-db-storage encontrado. Saindo do teste'; exit 1; else echo 'Volume openldap-db-storage nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep openldap-slapd-storage)) && if [ "$$var" != "" ]; then echo 'Volume openldap-slapd-storage encontrado. Saindo do teste'; exit 1; else echo 'Volume openldap-slapd-storage nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep storage-db)) && if [ "$$var" != "" ]; then echo 'Volume storage-db encontrado. Saindo do teste'; exit 1; else echo 'Volume storage-db nao encontrado'; fi

	@var=$$(echo $$(docker volume ls | grep volume-solr)) && if [ "$$var" != "" ]; then echo 'Volume volume-solr encontrado. Saindo do teste'; exit 1; else echo 'Volume volume-solr nao encontrado'; fi

	@echo "Verificacao de volumes ausentes realizada com sucesso"
	@echo "*****************************************************"
	@echo "*****************************************************"



