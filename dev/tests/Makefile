SHELL := /bin/bash

ifneq ("$(wildcard env-test.env)","")
include env-test.env
endif

ifndef spe
spe=sei4
endif

ISLINUX:=$(shell echo $$(uname | grep -i linux | wc -l))
CMDSELENIUMCHROMELOCAL=docker run -d --rm --name seleniumchrome  -p 4444:4444 -p 5900:5900 --network=host -v /dev/shm:/dev/shm selenium/standalone-chrome:4.0.0-rc-1-prerelease-20210618
CMDSELENIUMTEST=docker run --rm -it -v "$$PWD"/Selenium/PythonExported:/t -w /t --network=host linhares/pytestseleniumdocker:latest bash -c "echo '127.0.0.1 seleniumchrome' >> /etc/hosts && pytest --disable-pytest-warnings -W ignore::DeprecationWarning -o junit_family=xunit2 --junitxml=/t/resultado.xml test_suiteBasics-executar.py"

define MENSAGEM_TESTE_SELENIUM
*****************************************************
*****************************************************
Vamos agora rodar um teste simples no Selenium para saber se estao de acordo:
- login;
- criacao de processo com anexo e doc interno;
- logout
*****************************************************
*****************************************************

endef
export MENSAGEM_TESTE_SELENIUM

define MENSAGEM_TESTE_ALL
*****************************************************
*****************************************************
Vamos agora rodar um teste em todas as bases de dados.
Para cada base de dados vamos:
- copiar o env respectivo;
- subir o projeto;
- rodar teste funcional simples no selenium;
- Destruir o ambiente

Verifique antes de rodar se ha algo ja levantado pois o
teste vai tentar criar os ambientes do zero
*****************************************************
*****************************************************

endef
export MENSAGEM_TESTE_ALL


help: ## Mostra essa ajuda. Voce pode usar tab para completar os comandos
		@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | sed -e 's|^\ ||'


test_selenium_basico1: ## target de apoio roda um teste em selenium apontando para a instancia escolhida para criar processo com anexos

		@echo "$$MENSAGEM_TESTE_SELENIUM"

		@echo "Subindo SeleniumChrome..."

		@var=$$(echo $$(docker ps -a | grep "seleniumchrome"));  if [ "$$var" != "" ]; then docker stop seleniumchrome; fi;

ifeq ("$(ISLINUX)",  "1")
		@echo "Subindo SeleniumChrome na rede do host"
		@$(CMDSELENIUMCHROMELOCAL)
else
		@echo "Ambiente subiu com url localhost, pulando testes Selenium para quem nao usa linux. Recomendavel rodar esse teste em linux."
endif

		@for i in {1..4}; do echo 'Tentando acessar SeleniumChrome...'; var=$$(echo $$(docker logs  seleniumchrome | grep "Started Selenium Standalone")); if [ "$$var" != "" ]; then echo 'SeleniumChrome respondeu com sucesso....'; break; else echo 'Aguardando SeleniumChrome...'; fi; sleep 5;  done; \
		var=$$(echo $$(docker logs  seleniumchrome | grep "Started Selenium Standalone"));  if [ "$$var" = "" ]; then echo 'Selenium nao subiu. Saindo do teste'; exit 1; fi;

		@echo "Selenium no ar vamos rodar o teste...";
		@rm -f Selenium/PythonExported/test_suiteBasics-executar.py && cp Selenium/PythonExported/test_suiteBasics.py Selenium/PythonExported/test_suiteBasics-executar.py
		@sed -i'' -e "s|<<PROTOCOLO>>|http|" Selenium/PythonExported/test_suiteBasics-executar.py
		@sed -i'' -e "s|<<HOST>>|localhost:8000|" Selenium/PythonExported/test_suiteBasics-executar.py
		@sed -i'' -e "s|<<SENHA>>|teste|" Selenium/PythonExported/test_suiteBasics-executar.py

ifeq ("$(ISLINUX)",  "1")
		@echo "Subindo teste na rede do host"
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


tests-base: ## target de apoio roda o teste em determinada base de dados via parametro
		@echo "*****************************************************"
		@echo "Base de dados dessa execucao: $(BASE)"
		@echo "*****************************************************"
		make -C ../ destroy
		rm -rf ../env.env
		make -C ../ base=$(BASE) config
		make -C ../ up-update
		make test_selenium_basico1
		make -C ../ destroy
		@echo "*****************************************************"
		@echo "Finalizado testes em ambiente na base: $(BASE)"
		@echo "*****************************************************"


tests-all-bases: ## RODE ESSE TARGET PARA TESTAR TODA A STACK DO DEV
		@echo "$$MENSAGEM_TESTE_ALL"
		@echo ""
		make BASE=mysql-$(spe) tests-base
		make BASE=mariadb-$(spe) tests-base
		make BASE=sqlserver-$(spe) tests-base
		make BASE=oracle-$(spe) tests-base
		make BASE=postgres-$(spe) tests-base
		@echo "*****************************************************"
		@echo "*****************************************************"
		@echo "TESTES FINALIZADOS"
		@echo "*****************************************************"
		@echo "*****************************************************"
