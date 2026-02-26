# Variaveis de Ambiente - Referencia Completa

Referencia de todas as variaveis de ambiente do projeto SEI-Docker, organizadas por categoria. Sao aproximadamente **230+ variaveis** distribuidas entre os arquivos de configuracao.

---

## Arquivos de Configuracao

| Arquivo | Escopo | Descricao |
|---------|--------|-----------|
| `infra/envlocal.env` | Infraestrutura | Configuracao principal com 100+ variaveis |
| `containers/envcontainers.env.modelo` | Build | Template para build de imagens Docker |
| `dev/envs/env-{banco}-{versao}.env` | Desenvolvimento | Config por combinacao banco/versao (8 arquivos) |

---

## 1. Caminhos e Controle de Build

**Arquivo:** `infra/envlocal.env`

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `LOCALIZACAO_FONTES_SEI` | `~/sei/FonteSEI` | Caminho absoluto no host para o codigo-fonte do SEI. Deve conter as pastas `infra`, `sei` e `sip` |
| `LOCALIZACAO_CERTS` | `~/sei/certs` | Caminho no host para certificados SSL. Criar vazio; certificados auto-assinados sao gerados automaticamente |
| `MAKEFILE_MODO_VERBOSE` | `false` | Quando `true`, Makefile imprime todos os comandos executados |
| `DOCKER_COMPOSE_BUILD` | `true` | Manter build dentro do docker-compose (nao funcional com Rancher) |

---

## 2. Docker Registry

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `DOCKER_REGISTRY` | `processoeletronico` | Prefixo do registry Docker para todas as imagens |

---

## 3. Volumes Docker

**Arquivo:** `infra/envlocal.env`

Todos os volumes devem ser criados mesmo quando nao utilizados. Volumes persistem dados entre rebuilds e sao os unicos itens que precisam de backup.

### Volume do Banco de Dados

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_DB` | `local-storage-db` | Nome do volume Docker para dados do banco |
| `VOLUME_DB_EXTERNAL` | `true` | Volume gerenciado externamente |
| `VOLUME_DB_DRIVER` | `local` | Driver do volume |
| `VOLUME_DB_MOUNT` | `local-storage-db` | Ponto de montagem do volume |
| `DB_DATA_DIRECTORY` | `/var/lib/mysql` | Diretorio interno no container onde o banco armazena dados |

### Volume de Arquivos Externos

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_ARQUIVOSEXTERNOS` | `local-arquivosexternos-storage` | Volume para armazenamento de arquivos (anexos do SEI) |
| `VOLUME_ARQUIVOSEXTERNOS_EXTERNAL` | `false` | Volume gerenciado externamente |
| `VOLUME_ARQUIVOSEXTERNOS_DRIVER` | `local` | Driver do volume |
| `VOLUME_ARQUIVOSEXTERNOS_MOUNT` | `local-arquivosexternos-storage` | Ponto de montagem |

### Volume de Codigo-Fonte

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_FONTES` | `local-fontes-storage` | Volume para codigo-fonte do SEI |
| `VOLUME_FONTES_MOUNT` | `local-fontes-storage` | Ponto de montagem |

### Volume de Certificados

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_CERTS` | `local-certs-storage` | Volume para certificados SSL |
| `VOLUME_CERTS_MOUNT` | `local-certs-storage` | Ponto de montagem |

### Volume do Solr

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_SOLR` | `local-volume-solr` | Volume para dados do Solr |
| `SOLR_DATA_DIRECTORY` | `/dados` | Diretorio interno de dados do Solr |

### Volumes do OpenLDAP

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_OPENLDAP_SLAPD` | `local-openldap-slapd-storage` | Volume para config slapd |
| `VOLUME_OPENLDAP_SLAPD_EXTERNAL` | `false` | Volume gerenciado externamente |
| `VOLUME_OPENLDAP_SLAPD_DRIVER` | `local` | Driver do volume |
| `VOLUME_OPENLDAP_SLAPD_MOUNT` | `local-openldap-slapd-storage` | Ponto de montagem |
| `VOLUME_OPENLDAP_DB` | `local-openldap-db-storage` | Volume para dados LDAP |
| `VOLUME_OPENLDAP_DB_EXTERNAL` | `false` | Volume gerenciado externamente |
| `VOLUME_OPENLDAP_DB_DRIVER` | `local` | Driver do volume |
| `VOLUME_OPENLDAP_DB_MOUNT` | `local-openldap-db-storage` | Ponto de montagem |

### Volume do Controlador de Instalacao

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `VOLUME_CONTROLADOR_INSTALACAO` | `local-controlador-instalacao-storage` | Volume para rastrear estado de instalacao de modulos |
| `VOLUME_CONTROLADOR_INSTALACAO_EXTERNAL` | `false` | Volume gerenciado externamente |
| `VOLUME_CONTROLADOR_INSTALACAO_DB_DRIVER` | `local` | Driver do volume |
| `VOLUME_CONTROLADOR_INSTALACAO_MOUNT` | `local-controlador-instalacao-storage` | Ponto de montagem |

---

## 4. Load Balancer (Traefik)

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `DOCKER_IMAGE_BALANCEADOR` | `${DOCKER_REGISTRY}/traefik:latest` | Imagem Docker do Traefik |
| `BALANCEADOR_PRESENTE` | `true` | Habilitar load balancer |
| `BALANCEADOR_PORTA_80_MAP_EXPOR` | `true` | Expor porta 80 no host |
| `BALANCEADOR_PORTA_80_MAP` | `80:80` | Mapeamento da porta HTTP |
| `BALANCEADOR_PORTA_443_MAP_EXPOR` | `true` | Expor porta 443 no host |
| `BALANCEADOR_PORTA_443_MAP` | `443:443` | Mapeamento da porta HTTPS |

---

## 5. Imagens Docker dos Servicos

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `DOCKER_IMAGE_APP` | `${DOCKER_REGISTRY}/app-ci:latest` | Imagem da aplicacao SEI principal |
| `DOCKER_IMAGE_APP_AGENDADOR` | `${DOCKER_REGISTRY}/app-ci-agendador:latest` | Imagem do agendador de tarefas |
| `DOCKER_IMAGE_BD` | `${DOCKER_REGISTRY}/mariadb10.5-sei40:latest` | Imagem do banco de dados |
| `DOCKER_IMAGE_SOLR` | `${DOCKER_REGISTRY}/solr8.2.0:latest` | Imagem do Apache Solr |
| `DOCKER_IMAGE_MEMCACHED` | `${DOCKER_REGISTRY}/memcached:latest` | Imagem do Memcached |
| `DOCKER_IMAGE_JOD` | `${DOCKER_REGISTRY}/jod:latest` | Imagem do JOD Converter |
| `DOCKER_IMAGE_MAIL` | `${DOCKER_REGISTRY}/mailcatcher:latest` | Imagem do MailCatcher |
| `DOCKER_IMAGE_OPENLDAP` | `${DOCKER_REGISTRY}/openldap:latest` | Imagem do OpenLDAP |
| `DOCKER_IMAGE_OPENLDAP_PHPLDAPADMIN` | `${DOCKER_REGISTRY}/phpldapadmin:latest` | Imagem do phpLDAPadmin |
| `DOCKER_IMAGE_MEMCACHEDADMIN` | `${DOCKER_REGISTRY}/phpmemcachedadmin:latest` | Imagem do admin Memcached |
| `DOCKER_IMAGE_DBADMIN` | `${DOCKER_REGISTRY}/dbadminer:latest` | Imagem do Adminer (admin BD) |

---

## 6. Toggles de Servicos Opcionais

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `JOD_PRESENTE` | `true` | Habilitar conversao de documentos |
| `DBADMIN_PRESENTE` | `false` | Habilitar Adminer (disponivel em `/dbadmin`) |
| `MEMCACHEDADMIN_PRESENTE` | `false` | Habilitar admin Memcached (disponivel em `/memcachedadmin`) |
| `MAIL_CATCHER_PRESENTE` | `false` | Habilitar MailCatcher (disponivel em `/mailadmin`) |
| `OPENLDAP_PRESENTE` | `false` | Habilitar OpenLDAP. Quando `true`, SEI usa LDAP para autenticacao |
| `BALANCEADOR_PRESENTE` | `true` | Habilitar Traefik como load balancer |

---

## 7. Portas da Aplicacao

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_PORTA_80_MAP_EXPOR` | `false` | Expor porta 80 diretamente no container app (usar sem load balancer) |
| `APP_PORTA_80_MAP` | `80:80` | Mapeamento HTTP no app |
| `APP_PORTA_443_MAP_EXPOR` | `false` | Expor porta 443 diretamente no container app |
| `APP_PORTA_443_MAP` | `443:443` | Mapeamento HTTPS no app |

---

## 8. Configuracao da Aplicacao

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_PROTOCOLO` | `https` | Protocolo: `http` ou `https`. Controla Traefik e geracao de certificados |
| `APP_HOST` | `localhost` | Hostname/URL do SEI. Pode ser DNS real ou mapeado em /etc/hosts |
| `APP_ORGAO` | `ME` | Sigla do orgao |
| `APP_ORGAO_DESCRICAO` | `Orgao Processo Eletronico - MySql` | Descricao completa do orgao |
| `APP_ORGAOS_ADICIONAIS_SIGLA` | *(vazio)* | Siglas de orgaos adicionais separadas por `/` |
| `APP_ORGAOS_ADICIONAIS_NOME` | *(vazio)* | Nomes de orgaos adicionais separados por `/` |
| `APP_FEDERACAO_HABILITAR` | `false` | Habilitar modo federacao do SEI |
| `APP_NOMECOMPLEMENTO` | `SEI - PEN - DTH` | Nome complementar exibido no SEI |

---

## 9. Sessoes Memcached

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_MEMCACHED_HOST` | `memcached` | Hostname do servico Memcached |
| `APP_MEMCACHED_SESSION` | `false` | Quando `true`, sessoes PHP sao armazenadas no Memcached (necessario para balanceamento sem sticky session). Funciona apenas com SEI 5 |

---

## 10. Gerenciamento de Fontes via Git

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_FONTES_GIT_PATH` | *(vazio)* | Caminho SSH do repositorio Git dos fontes. Ex: `git@github.com:supergovbr/super` |
| `APP_FONTES_GIT_PRIVKEY_BASE64` | *(vazio)* | Chave privada SSH codificada em Base64 para clonar o repositorio |
| `APP_FONTES_GITHUB_TOKEN` | *(vazio)* | Token GitHub para download de tarball. Tem precedencia sobre `APP_FONTES_GIT_PRIVKEY_BASE64` |
| `APP_FONTES_GIT_CHECKOUT` | *(vazio)* | Branch, tag ou commit para checkout. Ex: `main`, `4.0.3.3` |

---

## 11. Conexao com Banco de Dados

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_DB_TIPO` | `MySql` | Tipo do banco. Valores: `MySql`, `SqlServer`, `Oracle`, `PostgreSql` |
| `APP_DB_HOST` | `db` | Hostname do container de banco |
| `APP_DB_PORTA` | `3306` | Porta do banco de dados |
| `APP_DB_SEI_BASE` | `sei` | Nome da base de dados do SEI |
| `APP_DB_SEI_USERNAME` | `sei_user` | Usuario do banco SEI |
| `APP_DB_SEI_PASSWORD` | `sei_user` | Senha do banco SEI |
| `APP_DB_SIP_BASE` | `sip` | Nome da base de dados do SIP |
| `APP_DB_SIP_USERNAME` | `sip_user` | Usuario do banco SIP |
| `APP_DB_SIP_PASSWORD` | `sip_user` | Senha do banco SIP |
| `APP_DB_ROOT_USERNAME` | `root` | Usuario root/admin do banco |
| `APP_DB_ROOT_PASSWORD` | `P@ssword` | Senha root/admin do banco |

---

## 12. Chaves de Acesso SEI/SIP

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_SEI_CHAVE_ACESSO` | `7babf862e12b...6135dc` | Token de autenticacao inter-sistema do SEI |
| `APP_SIP_CHAVE_ACESSO` | `d27791b89402...fb933` | Token de autenticacao inter-sistema do SIP |

---

## 13. Configuracao do Solr

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_SOLR_URL` | `http://solr:8983/solr` | URL base do Solr |
| `APP_SOLR_CORE_PROTOCOLOS` | `sei-protocolos` | Core para documentos de protocolo |
| `APP_SOLR_TEMPO_COMMIT_PROTOCOLOS` | `300` | Intervalo de auto-commit (segundos) |
| `APP_SOLR_CORE_BASECONHECIMENTO` | `sei-bases-conhecimento` | Core para base de conhecimento |
| `APP_SOLR_TEMPO_COMMIT_BASECONHECIMENTO` | `60` | Intervalo de auto-commit (segundos) |
| `APP_SOLR_CORE_PUBLICACOES` | `sei-publicacoes` | Core para publicacoes |
| `APP_SOLR_TEMPO_COMMIT_PUBLICACOES` | `60` | Intervalo de auto-commit (segundos) |

---

## 14. Configuracao de E-mail (SMTP)

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `APP_MAIL_TIPO` | `2` | Metodo de envio. `1` = sendmail, `2` = SMTP |
| `APP_MAIL_SERVIDOR` | `mail` | Servidor SMTP |
| `APP_MAIL_PORTA` | `25` | Porta SMTP |
| `APP_MAIL_CODIFICACAO` | `8bit` | Codificacao: `8bit`, `7bit`, `binary`, `base64`, `quoted-printable` |
| `APP_MAIL_MAXDESTINATARIOS` | `999` | Maximo de destinatarios por e-mail |
| `APP_MAIL_MAXTAMANHOANEXOSMB` | `999` | Tamanho maximo de anexos (MB) |
| `APP_MAIL_SEGURANCA` | *(vazio)* | Seguranca SMTP: `/TLS`, `SSL`, ou vazio |
| `APP_MAIL_AUTENTICAR` | *(vazio)* | Habilitar autenticacao SMTP |
| `APP_MAIL_USUARIO` | *(vazio)* | Usuario SMTP |
| `APP_MAIL_SENHA` | *(vazio)* | Senha SMTP |
| `APP_MAIL_PROTEGIDO` | *(vazio)* | E-mail de redirecionamento em dev. Quando definido, TODOS os e-mails sao desviados para este endereco |

---

## 15. OpenLDAP

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `OPENLDAP_PRESENTE` | `false` | Habilitar OpenLDAP |
| `OPENLDAP_ADMIN_PASSWORD` | `adminldap` | Senha admin LDAP. Login: `cn=admin,dc=pen,dc=gov,dc=br` |
| `OPENLDAP_DESLIGAR_NO_ORGAO_0` | `false` | Forcar desativacao do LDAP para orgao 0 |

---

## 16. Protocolo Digital

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `SERVICO_PD_INSTALAR` | `false` | Instalar integracao Protocolo Digital |
| `SERVICO_PD_SIGLA` | `GOV.BR` | Sigla do servico |
| `SERVICO_PD_NOME` | `Protocolo.GOV.BR` | Nome do servico |
| `SERVICO_PD_OPERACOES` | `3,2,15,0,1` | IDs das operacoes habilitadas |

---

## 17. Credenciais Git para Modulos

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `GITUSER_REPO_MODULOS` | `dummy` | Usuario Git para repositorios de modulos privados |
| `GITPASS_REPO_MODULOS` | `dummy` | Senha/token Git para repositorios de modulos |

---

## 18. Modulo Estatisticas

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_ESTATISTICAS_INSTALAR` | `true` | Instalar modulo de estatisticas |
| `MODULO_ESTATISTICAS_VERSAO` | `master` | Branch/tag do repositorio |
| `MODULO_ESTATISTICAS_URL` | `https://estatistica.dev.processoeletronico.gov.br` | URL do servico de estatisticas |
| `MODULO_ESTATISTICAS_SIGLA` | `SEIPUBLICO` | Sigla da credencial (publica para dev) |
| `MODULO_ESTATISTICAS_CHAVE` | `seipublico` | Chave da credencial |

---

## 19. Modulo REST / WSSEI

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_REST_INSTALAR` | `false` | Instalar modulo REST |
| `MODULO_REST_VERSAO` | `master` | Branch/tag |
| `MODULO_REST_URL_NOTIFICACAO` | *(vazio)* | URL do servico de notificacoes push |
| `MODULO_REST_ID_APP` | *(vazio)* | ID da aplicacao para push |
| `MODULO_REST_CHAVE` | *(vazio)* | Chave de autorizacao |
| `MODULO_REST_TOKEN_SECRET` | *(vazio)* | Secret para tokens |

---

## 20. Modulo Gestao Documental

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_GESTAODOCUMENTAL_INSTALAR` | `false` | Instalar modulo de gestao documental |
| `MODULO_GESTAODOCUMENTAL_VERSAO` | `master` | Branch/tag |

---

## 21. Modulo Resposta

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_RESPOSTA_INSTALAR` | `false` | Instalar modulo de resposta |
| `MODULO_RESPOSTA_VERSAO` | `master` | Branch/tag |

---

## 22. Modulo Login Unico (GOV.BR)

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_LOGINUNICO_INSTALAR` | `false` | Instalar modulo Login Unico |
| `MODULO_LOGINUNICO_VERSAO` | `master` | Branch/tag |
| `MODULO_LOGINUNICO_CLIENTID` | `sistemas/homologacao/sei/controlador_externo` | Client ID OAuth2 |
| `MODULO_LOGINUNICO_SECRET` | `XXXX` | Client Secret OAuth2 |
| `MODULO_LOGINUNICO_URLPROVIDER` | `https://sso.staging.acesso.gov.br/` | URL do provedor OAuth2 (SSO) |
| `MODULO_LOGINUNICO_REDIRECTURL` | `http://sei.xxx.nuvem.gov.br/.../controlador_loginunico.php` | URL de callback |
| `MODULO_LOGINUNICO_URLLOGOUT` | `http://sei.xxx.nuvem.gov.br/.../logout.php` | URL de logout |
| `MODULO_LOGINUNICO_SCOPE` | `openid+email+phone+profile+govbr_empresa+govbr_confiabilidades` | Escopos OAuth2 |
| `MODULO_LOGINUNICO_URLSERVICOS` | `https://api.staging.acesso.gov.br/` | URL dos servicos GOV.BR |
| `MODULO_LOGINUNICO_URLREVALIDACAO` | `https://oauth.staging.acesso.gov.br/v1/` | Endpoint de revalidacao |
| `MODULO_LOGINUNICO_CIENTIDVALIDACAO` | `sei.xxx.nuvem.gov.br/validacaosenha` | Client ID para revalidacao |
| `MODULO_LOGINUNICO_SECRETVALIDACAO` | `XXX` | Secret para revalidacao |
| `MODULO_LOGINUNICO_ORGAO` | `0` | ID do orgao para integracao |

---

## 23. Modulo Assinatura Avancada

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_ASSINATURA_INSTALAR` | `false` | Instalar modulo de assinatura eletronica |
| `MODULO_ASSINATURA_VERSAO` | `master` | Branch/tag |
| `MODULO_ASSINATURA_URLPROVIDER` | `https://cas.staging.iti.br/oauth2.0` | Provedor OAuth2 para assinatura |
| `MODULO_ASSINATURA_CLIENTID` | `assinaturaAvancadaXXX` | Client ID |
| `MODULO_ASSINATURA_SECRET` | `XXX` | Client Secret |
| `MODULO_ASSINATURA_VALIDAR_API_URL` | `https://informarurl` | URL da API de validacao |
| `MODULO_ASSINATURA_VALIDAR_API_KEY` | `XXX` | Chave da API de validacao |
| `MODULO_ASSINATURA_TOKEN_URL` | `XX` | URL para obtencao de token |
| `MODULO_ASSINATURA_TOKEN_SIGN_URL` | `XX` | URL para assinatura de token |
| `MODULO_ASSINATURA_INTEGRA_ICP_URL` | `https://informar` | URL base ICP-Brasil |
| `MODULO_ASSINATURA_INTEGRA_ICP_URL_CLEARINGS` | `/get-clearings` | Endpoint de clearings ICP |
| `MODULO_ASSINATURA_INTEGRA_ICP_URL_ASSINAR` | `/sign` | Endpoint de assinatura ICP |
| `MODULO_ASSINATURA_CLOUDPSC_URL` | `XXX` | URL do Cloud PSC |
| `MODULO_ASSINATURA_CLOUDPSC_START_URL` | `XXX` | URL de inicio Cloud PSC |
| `MODULO_ASSINATURA_CLOUDPSC_SIGN_URL` | `XXX` | URL de assinatura Cloud PSC |
| `MODULO_ASSINATURA_API_KEY_ITYHY` | `XXX` | Chave API ITI/Hy |

---

## 24. Modulo PEN / Barramento

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_PEN_INSTALAR` | `false` | Instalar modulo PEN (tramitacao entre orgaos) |
| `MODULO_PEN_VERSAO` | `master` | Branch/tag |
| `MODULO_PEN_WEBSERVICE` | `https://homolog.api.processoeletronico.gov.br/.../v3/` | Endpoint SOAP do Barramento |
| `MODULO_PEN_CERTIFICADO_SENHA` | `1234` | Senha do certificado PEM |
| `MODULO_PEN_CERTIFICADO_BASE64` | *(base64 longo)* | Certificado PEM em Base64 (cert + chave privada) |
| `MODULO_PEN_GEARMAN_IP` | *(vazio)* | IP do servidor Gearman |
| `MODULO_PEN_GEARMAN_PORTA` | *(vazio)* | Porta do Gearman |
| `MODULO_PEN_QTD_WORKER_PROC` | `1` | Numero de workers Gearman para PEN |
| `MODULO_PEN_REPOSITORIO_ORIGEM` | *(vazio)* | Repositorio de origem (auto-config) |
| `MODULO_PEN_TIPO_PROCESSO_EXTERNO` | *(vazio)* | Tipo de processo para recebimentos externos |
| `MODULO_PEN_UNIDADE_GERADORA` | *(vazio)* | Unidade geradora para processos recebidos |
| `MODULO_PEN_UNIDADE_ASSOCIACAO_PEN` | *(vazio)* | ID da unidade PEN para associacao |
| `MODULO_PEN_UNIDADE_ASSOCIACAO_SEI` | *(vazio)* | ID da unidade SEI para associacao |

---

## 25. Modulo Peticionamento

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_PETICIONAMENTO_INSTALAR` | `false` | Instalar modulo de peticionamento eletronico |
| `MODULO_PETICIONAMENTO_VERSAO` | `master` | Branch/tag |
| `MODULO_PETICIONAMENTO_URL` | `https://github.com/anatelgovbr/mod-sei-peticionamento` | URL do repositorio |

---

## 26. Modulo Protocolo Integrado

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_PI_INSTALAR` | `false` | Instalar modulo Protocolo Integrado |
| `MODULO_PI_VERSAO` | `master` | Branch/tag |
| `MODULO_PI_URL` | `https://protocolointegrado.preprod.nuvem.gov.br/.../integradorService?wsdl` | WSDL do web service |
| `MODULO_PI_USUARIO` | `usuariodeconexaopi` | Usuario de conexao |
| `MODULO_PI_SENHA` | `senhadeconexaoaopi` | Senha de conexao |
| `MODULO_PI_EMAIL` | `email@example.com` | E-mail de contato |

---

## 27. Modulo INCOM (Imprensa Nacional)

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `MODULO_INCOM_INSTALAR` | `false` | Instalar modulo INCOM |
| `MODULO_INCOM_VERSAO` | `v1.0.4` | Branch/tag |
| `MODULO_INCOM_VEICULOID` | `2` | ID do veiculo de publicacao |
| `MODULO_INCOM_SERIEID` | `10` | ID da serie documental |
| `MODULO_INCOM_SIORG` | `235876` | Codigo SIORG do orgao |
| `MODULO_INCOM_URLWS` | `https://seiwsincom2.in.gov.br/.../servicoIN?wsdl` | WSDL do web service INCOM |
| `MODULO_INCOM_USERWS` | `XXX` | Usuario do web service |
| `MODULO_INCOM_PASSWS` | `XXX` | Senha do web service |
| `MODULO_INCOM_INCLUSAOPUBLICACAO` | `S` | Incluir publicacao (`S` = Sim) |

---

## 28. Kubernetes

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `KUBERNETES_NAMESPACE` | `seins` | Namespace Kubernetes |
| `KUBERNETES_PVC_STORAGECLASS` | `nfs-client` | Storage class para PVCs |
| `KUBERNETES_RESOURCES_INFORMAR` | `true` | Incluir limites de recursos nos manifests |

### Limites de Recursos por Servico

| Servico | CPU Limit | Memory Limit | CPU Request | Memory Request |
|---------|-----------|-------------|-------------|----------------|
| Memcached | `500m` | `512Mi` | `500m` | `512Mi` |
| JOD | `500m` | `512Mi` | `500m` | `512Mi` |
| Solr | `500m` | `1Gi` | `500m` | `1Gi` |
| Banco de Dados | `500m` | `1Gi` | `500m` | `1Gi` |
| Aplicacao | `500m` | `1Gi` | `500m` | `1Gi` |
| Agendador | `500m` | `1Gi` | `500m` | `1Gi` |

**Variaveis:** `KUBERNETES_LIMITS_CPU_{SERVICO}`, `KUBERNETES_LIMITS_MEMORY_{SERVICO}`, `KUBERNETES_REQUEST_CPU_{SERVICO}`, `KUBERNETES_REQUEST_MEMORY_{SERVICO}`

---

## 29. Variaveis de Build de Containers

**Arquivo:** `containers/envcontainers.env.modelo`

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `DOCKER_CONTAINER_VERSAO_PRODUTO` | `3.6.11` | Versao do produto para tags das imagens |

### Imagens Base

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `IMAGEM_BASE_CENTOS` | `${DOCKER_REGISTRY}/base-centos7` | Imagem base CentOS |
| `IMAGEM_BASE_ROCKY93` | `${DOCKER_REGISTRY}/base-rocky93` | Imagem base Rocky Linux |
| `IMAGEM_BASE_MARIADB` | `${DOCKER_REGISTRY}/base-mariadb10.5` | Imagem base MariaDB |
| `IMAGEM_BASE_MYSQL8` | `${DOCKER_REGISTRY}/base-mysql8` | Imagem base MySQL 8 |
| `IMAGEM_BASE_SQLSERVER` | `${DOCKER_REGISTRY}/base-sqlserver2019` | Imagem base SQL Server |
| `IMAGEM_BASE_ORACLE` | `${DOCKER_REGISTRY}/base-oracle11g` | Imagem base Oracle |
| `IMAGEM_BASE_POSTGRES` | `${DOCKER_REGISTRY}/base-postgres15` | Imagem base PostgreSQL |
| `IMAGEM_BASE_APP` | `${DOCKER_REGISTRY}/base-app` | Imagem base App PHP 7 |
| `IMAGEM_BASE_APP_PHP8` | `${DOCKER_REGISTRY}/base-app-php8` | Imagem base App PHP 8 |
| `IMAGEM_BASE_APP_AGENDADOR` | `${DOCKER_REGISTRY}/app-ci` | Imagem base Agendador PHP 7 |
| `IMAGEM_BASE_APP_PHP8_AGENDADOR` | `${DOCKER_REGISTRY}/app-ci-php8` | Imagem base Agendador PHP 8 |
| `IMAGEM_BASE_HAPROXY` | `${DOCKER_REGISTRY}/haproxy-base` | Imagem base HAProxy |
| `IMAGEM_BASE_TRAEFIK` | `${DOCKER_REGISTRY}/traefik-base` | Imagem base Traefik |
| `IMAGEM_BASE_OPENLDAP` | `${DOCKER_REGISTRY}/openldap-base` | Imagem base OpenLDAP |
| `IMAGEM_BASE_PHPMEMCACHEDADMIN` | `${DOCKER_REGISTRY}/phpmemcachedadmin-base` | Imagem base admin Memcached |

Cada variavel possui uma correspondente `*_VERSAO` com padrao `latest`.

---

## 30. Variaveis do Ambiente de Desenvolvimento

**Arquivos:** `dev/envs/env-{banco}-{versao}.env`

| Variavel | MySQL | PostgreSQL | Oracle | SQL Server | Descricao |
|----------|-------|-----------|--------|------------|-----------|
| `ENVIRONMENT_VERSION` | `3.6.7` | `3.6.7` | `3.6.7` | `3.6.7` | Versao do ambiente dev |
| `DATABASE_IMAGE` | `mysql8-sei50` | `postgres15-sei50` | `oracle11g-sei50` | `sqlserver2019-sei50` | Imagem do banco |
| `DATABASE_TYPE` | `MySql` | `PostgreSql` | `Oracle` | `SqlServer` | Tipo do banco |
| `DATABASE_HOST` | `mysql` | `postgres` | `oracle` | `sqlserver` | Hostname do banco |
| `DATABASE_PORT` | `3306` | `5432` | `1521` | `1433` | Porta do banco |
| `DATABASE_DATA_DIR` | `/var/lib/mysql` | `/var/lib/postgresql/data` | `/u01/app/oracle` | `/var/opt/mssql/data` | Diretorio de dados |
| `APP_IMAGE` | `app-dev-php8` | `app-dev-php8` | `app-dev-php8` | `app-dev-php8` | Imagem do app |
| `SOLR_IMAGE` | `solr9.6.1` | `solr9.6.1` | `solr9.6.1` | `solr9.6.1` | Imagem do Solr |
| `JOD_IMAGE` | `jod4.4.8` | `jod4.4.8` | `jod4.4.8` | `jod4.4.8` | Imagem do JOD |

### Credenciais do Banco (Dev)

| Variavel | MySQL/PostgreSQL | Oracle | Descricao |
|----------|-----------------|--------|-----------|
| `SEI_DATABASE_USER` | `sei_user` | `sei` | Usuario SEI |
| `SIP_DATABASE_USER` | `sip_user` | `sip` | Usuario SIP |
| `SEI_DATABASE_USER_SCRIPT` | `sei_user` / `postgres` | `sei` | Usuario para scripts de update |
| `SEI_DATABASE_PASSWORD_SCRIPT` | `sei_user` / `P@ssword` | `sei_user` | Senha para scripts |

### Variaveis de Runtime (docker-compose.yml dev)

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `SEI_PATH` | *(obrigatorio)* | Caminho do codigo-fonte no host, montado em `/opt/` |
| `HOST_IP` | *(obrigatorio)* | IP do host para XDebug |
| `HOST_URL` | `http://localhost:8000` | URL do SEI no ambiente dev |
| `XDEBUG_CONFIG` | `idekey=default client_host=${HOST_IP} client_port=9003 discover_client_host=1` | Config do XDebug |
| `XDEBUG_SESSION` | `default` | Nome da sessao/IDE key do XDebug |
| `XDEBUG_MODE` | `debug` | Modo do XDebug: `debug`, `profile`, `trace` |
