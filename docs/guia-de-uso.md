# Guia de Uso - SEI-Docker

Guia completo e unificado para instalacao, configuracao e uso do SEI-Docker em todas as suas modalidades: desenvolvimento, infraestrutura e build de containers.

---

## Indice

1. [Pre-requisitos](#1-pre-requisitos)
2. [Obter o Codigo-Fonte do SEI](#2-obter-o-codigo-fonte-do-sei)
3. [Inicio Rapido - Ambiente de Desenvolvimento](#3-inicio-rapido---ambiente-de-desenvolvimento)
4. [Inicio Rapido - Infraestrutura Completa](#4-inicio-rapido---infraestrutura-completa)
5. [Configuracao do envlocal.env](#5-configuracao-do-envlocalenv)
6. [Comandos Make - Referencia Rapida](#6-comandos-make---referencia-rapida)
7. [Acessando o SEI e Servicos](#7-acessando-o-sei-e-servicos)
8. [Trocar Banco de Dados](#8-trocar-banco-de-dados)
9. [Habilitar Servicos Opcionais](#9-habilitar-servicos-opcionais)
10. [Escalar a Aplicacao](#10-escalar-a-aplicacao)
11. [Alterar URL e Dominio](#11-alterar-url-e-dominio)
12. [Instalar Modulos](#12-instalar-modulos)
13. [Usar HTTPS com Certificado Proprio](#13-usar-https-com-certificado-proprio)
14. [Kubernetes](#14-kubernetes)
15. [Build de Imagens Customizadas](#15-build-de-imagens-customizadas)
16. [Credenciais Padrao](#16-credenciais-padrao)
17. [Resolucao de Problemas](#17-resolucao-de-problemas)
18. [Video Tutoriais](#18-video-tutoriais)
19. [Dicas e Boas Praticas](#19-dicas-e-boas-praticas)

---

## 1. Pre-requisitos

### Sistema Operacional
- Linux (recomendado) ou macOS
- Windows com WSL2

### Software

| Software | Versao Minima | Verificar |
|----------|--------------|-----------|
| Docker Engine | 20+ | `docker --version` |
| Docker Compose | v2+ | `docker compose version` |
| GNU Make | qualquer | `make --version` |
| envsubst | qualquer | `envsubst --version` |
| curl | qualquer | `curl --version` |
| Git | qualquer | `git --version` |

### Obrigatorio
- **Codigo-fonte do SEI** (propriedade do TRF4, obtido separadamente via [processoeletronico.gov.br](http://processoeletronico.gov.br))

> **Atencao:** O codigo-fonte do SEI e propriedade do TRF4. Sob nenhuma hipotese deve ser distribuido, emprestado ou salvo em qualquer lugar que nao seja privativo da TI do orgao.

---

## 2. Obter o Codigo-Fonte do SEI

O codigo-fonte deve estar organizado na seguinte estrutura:

```
~/sei/FonteSEI/
├── sei/
│   ├── web/
│   ├── scripts/
│   └── ...
├── sip/
│   ├── web/
│   └── ...
└── infra/
    └── ...
```

Defina o caminho como variavel de ambiente:
```bash
export SEI_PATH=~/sei/FonteSEI
```

---

## 3. Inicio Rapido - Ambiente de Desenvolvimento

O ambiente **dev** e a forma mais simples de subir o SEI localmente, com XDebug habilitado e codigo-fonte montado para edicao ao vivo.

### Passo a passo

```bash
# 1. Clonar o projeto
git clone https://github.com/pengovbr/sei-docker.git
cd sei-docker/dev

# 2. Definir caminho do codigo-fonte
export SEI_PATH=~/sei/FonteSEI

# 3. Subir o ambiente (MySQL como padrao)
make up
```

### Acessar

| Servico | URL | Credenciais |
|---------|-----|-------------|
| SEI | http://localhost:8000/sei | `teste` / `teste` |
| SIP | http://localhost:8000/sip | `teste` / `teste` |
| MailCatcher | http://localhost:1080 | -- |
| Solr Admin | http://localhost:8983/solr | -- |
| Memcached | localhost:11211 | -- |

### Parar e destruir

```bash
# Parar (preserva dados)
make down

# Parar e apagar tudo (volumes inclusos)
make destroy

# Reiniciar
make restart
```

### Trocar banco no dev

```bash
make destroy
make config base=postgres-sei5
make up
```

Opcoes: `mysql-sei4`, `mysql-sei5`, `oracle-sei4`, `oracle-sei5`, `postgres-sei4`, `postgres-sei5`, `sqlserver-sei4`, `sqlserver-sei5`

---

## 4. Inicio Rapido - Infraestrutura Completa

O ambiente **infra** e a forma completa de provisionar o SEI com todos os servicos, load balancer, HTTPS e possibilidade de escalonamento.

### Passo a passo

```bash
# 1. Clonar o projeto
git clone https://github.com/pengovbr/sei-docker.git
cd sei-docker/infra

# 2. Verificar e ajustar a variavel LOCALIZACAO_FONTES_SEI no envlocal.env
#    Apontar para o diretorio onde esta o codigo-fonte do SEI
vi envlocal.env

# 3. Subir tudo com um unico comando
make setup
```

O `make setup` executa automaticamente:
1. Valida compatibilidade de versao
2. Cria todos os volumes Docker necessarios
3. Gera o `docker-compose.yml` a partir do template
4. Sobe todos os servicos

### Acessar

Com a configuracao padrao (`APP_HOST=localhost`, `APP_PROTOCOLO=https`):

| Servico | URL |
|---------|-----|
| SEI | https://localhost/sei |
| SIP | https://localhost/sip |
| Traefik Dashboard | https://localhost/traefik |

### Parar e limpar

```bash
# Parar (preserva volumes/dados)
make clear

# Apagar TUDO (volumes inclusos - DESTRUTIVO)
make apagar_volumes
```

> **Importante:** Antes de rodar `make run` ou `make setup` apos alterar o `envlocal.env`, sempre rode `make clear` primeiro. Isso evita servicos orfaos rodando quando se remove servicos da configuracao.

---

## 5. Configuracao do envlocal.env

O arquivo `infra/envlocal.env` e o coracao da configuracao. Ele contem 100+ variaveis que controlam todo o ambiente.

### Abordagem recomendada

> **Nao altere tudo de uma vez.** Suba primeiro com as configuracoes padrao. Teste. Depois desligue, altere um parametro, suba novamente. Repita ate compreender todo o ecossistema.

### Variaveis mais importantes para comecar

| Variavel | O que faz | Padrao |
|----------|-----------|--------|
| `LOCALIZACAO_FONTES_SEI` | Caminho do codigo-fonte no host | `~/sei/FonteSEI` |
| `LOCALIZACAO_CERTS` | Caminho dos certificados SSL | `~/sei/certs` |
| `APP_PROTOCOLO` | `http` ou `https` | `https` |
| `APP_HOST` | Hostname/URL do SEI | `localhost` |
| `APP_ORGAO` | Sigla do orgao | `ME` |
| `APP_ORGAO_DESCRICAO` | Nome completo do orgao | `Orgao Processo Eletronico - MySql` |
| `APP_DB_TIPO` | Tipo do banco | `MySql` |
| `DOCKER_IMAGE_BD` | Imagem do banco de dados | `processoeletronico/mariadb10.5-sei40:latest` |

Para a referencia completa de todas as variaveis, consulte [variaveis-ambiente.md](variaveis-ambiente.md).

---

## 6. Comandos Make - Referencia Rapida

### Ambiente Dev (`cd dev`)

| Comando | Descricao |
|---------|-----------|
| `make help` | Lista todos os comandos |
| `make up` | Sobe o ambiente |
| `make up-update` | Sobe e executa scripts de atualizacao |
| `make update` | Executa scripts de atualizacao do SEI/SIP |
| `make config base=X` | Troca banco/versao |
| `make down` | Para o ambiente (preserva volumes) |
| `make restart` | Para e sobe novamente |
| `make destroy` | Para e apaga tudo (DESTRUTIVO) |
| `make check-sei-path` | Verifica codigo-fonte |
| `make check-sei-isalive` | Verifica se SEI responde |

### Infraestrutura (`cd infra`)

| Comando | Descricao |
|---------|-----------|
| `make help` | Lista todos os comandos |
| `make setup` | Setup completo (volumes + run) |
| `make run` | Gera docker-compose e sobe |
| `make build_docker_compose` | Apenas gera o docker-compose.yml |
| `make criar_volumes` | Cria todos os volumes |
| `make scale` | Escala app para 2 nos |
| `make scale qtd=N` | Escala app para N nos |
| `make stop` | Para containers |
| `make clear` | Para e remove containers (preserva volumes) |
| `make apagar_volumes` | Apaga TODOS os volumes (DESTRUTIVO) |
| `make logs` | Logs de todos os servicos |
| `make logs_app` | Logs da aplicacao |
| `make logs_balanceador` | Logs do Traefik |
| `make check-sei-isalive` | Verifica se SEI responde |

### Build de Containers (`cd containers`)

| Comando | Descricao |
|---------|-----------|
| `make help` | Lista todos os comandos |
| `make getenv` | Cria envcontainers.env a partir do modelo |
| `make build-conteiners` | Build de todas as imagens |
| `make erase-conteiners-local` | Apaga todas as imagens locais |
| `make publish-containers` | Publica todas no registry |

---

## 7. Acessando o SEI e Servicos

### Infraestrutura (com load balancer)

Todos os servicos sao acessados pela mesma URL base (definida em `APP_HOST`):

| Servico | Caminho | Toggle |
|---------|---------|--------|
| SEI | `/sei` | sempre ativo |
| SIP | `/sip` | sempre ativo |
| Traefik Dashboard | `/traefik` | `BALANCEADOR_PRESENTE=true` |
| Solr Admin | `/solr` | sempre ativo |
| Memcached Admin | `/memcachedadmin` | `MEMCACHEDADMIN_PRESENTE=true` |
| Database Admin | `/dbadmin` | `DBADMIN_PRESENTE=true` |
| MailCatcher | `/mailadmin` | `MAIL_CATCHER_PRESENTE=true` |
| phpLDAPadmin | `/phpldapadmin` | `OPENLDAP_PRESENTE=true` |

Exemplo com `APP_HOST=sei.treinamento.gov.br`:
- SEI: `https://sei.treinamento.gov.br/sei`
- Solr: `https://sei.treinamento.gov.br/solr`

### Desenvolvimento (sem load balancer)

| Servico | URL |
|---------|-----|
| SEI | http://localhost:8000/sei |
| SIP | http://localhost:8000/sip |
| MailCatcher | http://localhost:1080 |
| Solr | http://localhost:8983/solr |
| Banco MySQL | localhost:3306 |
| Banco PostgreSQL | localhost:5432 |
| Banco Oracle | localhost:1521 |
| Banco SQL Server | localhost:1433 |

---

## 8. Trocar Banco de Dados

### No ambiente dev

```bash
cd dev
make destroy                    # Apagar ambiente atual
make config base=postgres-sei5  # Trocar para PostgreSQL + SEI 5
make up                         # Subir com novo banco
```

### No ambiente infra

Edite `infra/envlocal.env` e altere:

```bash
# Para MySQL 8 + SEI 5.0
APP_DB_TIPO=MySql
APP_DB_PORTA=3306
DOCKER_IMAGE_BD=${DOCKER_REGISTRY}/mysql8-sei50:latest

# Para PostgreSQL + SEI 5.0
APP_DB_TIPO=PostgreSql
APP_DB_PORTA=5432
DOCKER_IMAGE_BD=${DOCKER_REGISTRY}/postgres15-sei50:latest

# Para Oracle + SEI 4.1
APP_DB_TIPO=Oracle
APP_DB_PORTA=1521
DOCKER_IMAGE_BD=${DOCKER_REGISTRY}/oracle11g-sei41:latest

# Para SQL Server + SEI 4.1
APP_DB_TIPO=SqlServer
APP_DB_PORTA=1433
DOCKER_IMAGE_BD=${DOCKER_REGISTRY}/sqlserver2019-sei41:latest
```

Depois:
```bash
make clear
make apagar_volumes
make setup
```

### Imagens de banco disponiveis

| Imagem | Banco | Versao SEI |
|--------|-------|------------|
| `mariadb10.5-sei40` | MariaDB 10.5 | 4.0 |
| `mariadb10.5-sei41` | MariaDB 10.5 | 4.1 |
| `mariadb10.5-sei50` | MariaDB 10.5 | 5.0 |
| `mysql8-sei41` | MySQL 8 | 4.1 |
| `mysql8-sei50` | MySQL 8 | 5.0 |
| `postgres15-sei40` | PostgreSQL 15 | 4.0 |
| `postgres15-sei41` | PostgreSQL 15 | 4.1 |
| `postgres15-sei50` | PostgreSQL 15 | 5.0 |
| `oracle11g-sei40` | Oracle 11g | 4.0 |
| `oracle11g-sei41` | Oracle 11g | 4.1 |
| `oracle11g-sei50` | Oracle 11g | 5.0 |
| `sqlserver2019-sei40` | SQL Server 2019 | 4.0 |
| `sqlserver2019-sei41` | SQL Server 2019 | 4.1 |
| `sqlserver2019-sei50` | SQL Server 2019 | 5.0 |

---

## 9. Habilitar Servicos Opcionais

Edite `infra/envlocal.env` e altere os toggles:

```bash
# Converter documentos (LibreOffice)
JOD_PRESENTE=true

# Captura de e-mails (teste)
MAIL_CATCHER_PRESENTE=true

# Administracao do banco via web
DBADMIN_PRESENTE=true

# Administracao do cache
MEMCACHEDADMIN_PRESENTE=true

# Diretorio LDAP (autenticacao)
OPENLDAP_PRESENTE=true

# Load balancer Traefik
BALANCEADOR_PRESENTE=true
```

Depois aplique:
```bash
make clear
make run
```

> Quando `OPENLDAP_PRESENTE=true`, o SEI passa a usar autenticacao LDAP automaticamente. Um usuario de teste com senha `123456` e criado. Para voltar ao login padrao, configure `OPENLDAP_PRESENTE=false` e `OPENLDAP_DESLIGAR_NO_ORGAO_0=true`, rode `make run`, depois volte `OPENLDAP_DESLIGAR_NO_ORGAO_0=false`.

---

## 10. Escalar a Aplicacao

O SEI pode ser escalado horizontalmente (multiplos nos de aplicacao atras do Traefik):

```bash
cd infra

# Escalar para 2 nos (padrao)
make scale

# Escalar para 3 nos
make scale qtd=3

# Voltar para 1 no
make scale qtd=1
```

> Para balanceamento sem sticky session, habilite sessoes no Memcached: `APP_MEMCACHED_SESSION=true` (requer SEI 5+).

---

## 11. Alterar URL e Dominio

### Trocar de localhost para um dominio personalizado

1. Pare o projeto:
```bash
make clear
make apagar_volumes
```

2. Edite `envlocal.env`:
```bash
APP_HOST=sei.treinamento.orgao.gov.br
APP_PROTOCOLO=https
```

3. Configure DNS ou `/etc/hosts`:
```bash
# /etc/hosts
192.168.1.100  sei.treinamento.orgao.gov.br
```

4. Suba novamente:
```bash
make setup
```

> **Importante:** Ao mudar o hostname, apague os volumes pois a URL fica gravada no banco de dados. O `make setup` recria tudo com a nova URL.

---

## 12. Instalar Modulos

Cada modulo e controlado por variaveis `MODULO_*_INSTALAR` e `MODULO_*_VERSAO` no `envlocal.env`.

### Exemplo: Habilitar modulo de Estatisticas

```bash
MODULO_ESTATISTICAS_INSTALAR=true
MODULO_ESTATISTICAS_VERSAO=master
```

### Exemplo: Habilitar modulo PEN (tramitacao entre orgaos)

```bash
MODULO_PEN_INSTALAR=true
MODULO_PEN_VERSAO=master
MODULO_PEN_WEBSERVICE=https://homolog.api.processoeletronico.gov.br/interoperabilidade/soap/v3/
MODULO_PEN_CERTIFICADO_SENHA=1234
MODULO_PEN_CERTIFICADO_BASE64=<certificado em base64>
```

### Modulos disponiveis

| Modulo | Variavel | Funcao |
|--------|----------|--------|
| Estatisticas | `MODULO_ESTATISTICAS_INSTALAR` | Painel de estatisticas |
| REST / WSSEI | `MODULO_REST_INSTALAR` | API REST |
| Gestao Documental | `MODULO_GESTAODOCUMENTAL_INSTALAR` | Gestao do ciclo de vida |
| Resposta | `MODULO_RESPOSTA_INSTALAR` | Respostas a demandas |
| Login Unico | `MODULO_LOGINUNICO_INSTALAR` | SSO GOV.BR |
| Assinatura | `MODULO_ASSINATURA_INSTALAR` | Assinatura digital ICP-Brasil |
| PEN | `MODULO_PEN_INSTALAR` | Tramitacao entre orgaos |
| Peticionamento | `MODULO_PETICIONAMENTO_INSTALAR` | Peticionamento eletronico |
| Protocolo Integrado | `MODULO_PI_INSTALAR` | Integracao Protocolo.GOV.BR |
| INCOM | `MODULO_INCOM_INSTALAR` | Publicacao na Imprensa Nacional |

Para detalhes de cada modulo e suas variaveis, consulte [variaveis-ambiente.md](variaveis-ambiente.md).

Apos alterar modulos:
```bash
make clear
make run
```

O container `app-atualizador` detecta e instala automaticamente os modulos habilitados.

---

## 13. Usar HTTPS com Certificado Proprio

### Certificado auto-assinado (padrao)

Por padrao, o projeto gera certificados auto-assinados automaticamente. Basta:
```bash
APP_PROTOCOLO=https
```

### Certificado proprio

1. Crie o diretorio de certificados definido em `LOCALIZACAO_CERTS`:
```bash
mkdir -p ~/sei/certs
```

2. Copie seus arquivos de certificado para esse diretorio

3. Configure no `envlocal.env`:
```bash
LOCALIZACAO_CERTS=~/sei/certs
APP_PROTOCOLO=https
```

### Usar HTTP (sem TLS)

```bash
APP_PROTOCOLO=http
```

---

## 14. Kubernetes

O projeto gera manifests Kubernetes a partir dos templates.

### Gerar manifests

```bash
cd infra

# Ajuste as variaveis KUBERNETES_* no envlocal.env
vi envlocal.env

# Gerar YAML
make kubernetes_montar_yaml
```

Os arquivos sao gerados em `infra/orquestrators/rancher-kubernetes/topublish/`.

### Aplicar no cluster

```bash
make kubernetes_apply
```

### Remover do cluster

```bash
make kubernetes_delete
```

### Configuracao Kubernetes no envlocal.env

```bash
KUBERNETES_NAMESPACE=seins
KUBERNETES_PVC_STORAGECLASS=nfs-client
KUBERNETES_RESOURCES_INFORMAR=true
KUBERNETES_LIMITS_MEMORY_APP=1Gi
KUBERNETES_LIMITS_CPU_APP=500m
# ... (ver variaveis-ambiente.md para lista completa)
```

> **Nota:** A geracao Kubernetes atualmente funciona apenas para MySQL. O codigo-fonte do SEI deve ser movido manualmente para o PVC `vol-sei-fontes`.

---

## 15. Build de Imagens Customizadas

Se voce deseja buildar suas proprias imagens ou usar seu proprio registry:

```bash
cd containers

# Criar arquivo de configuracao
make getenv

# Editar envcontainers.env
vi envcontainers.env
# Altere DOCKER_REGISTRY para o seu registry

# Build de todas as imagens
make build-conteiners

# Ou build individual
make build-conteiner-base-rocky93
make build-conteiner-app-ci-php8
make build-conteiner-mysql8-sei50

# Publicar no registry
make publish-containers
```

> Se alterar `DOCKER_REGISTRY` ou `DOCKER_CONTAINER_VERSAO_PRODUTO` nos containers, replique as alteracoes nos envfiles de `dev/` e `infra/`.

---

## 16. Credenciais Padrao

### Aplicacao SEI/SIP

| Campo | Valor |
|-------|-------|
| Usuario | `teste` |
| Senha | `teste` |

### Bancos de Dados

#### MySQL / MariaDB
| Usuario | Login | Senha |
|---------|-------|-------|
| Root | `root` | `P@ssword` |
| SEI | `sei_user` | `sei_user` |
| SIP | `sip_user` | `sip_user` |

Acesso: `mysql -h 127.0.0.1 -u root -p sei`

#### PostgreSQL
| Usuario | Login | Senha |
|---------|-------|-------|
| Root | `postgres` | `P@ssword` |
| SEI | `sei_user` | `sei_user` |
| SIP | `sip_user` | `sip_user` |

#### Oracle
| Usuario | Login | Senha |
|---------|-------|-------|
| SYS | `sys` | `P@ssword` |
| System | `system` | `P@ssword` |
| SEI | `sei` | `sei_user` |
| SIP | `sip` | `sip_user` |

Acesso: `sqlplus sys/P@ssword as sysdba`

#### SQL Server
| Usuario | Login | Senha |
|---------|-------|-------|
| SA | `sa` | `yourStrong(!)Password` |
| SEI | `sei_user` | `sei_user` |
| SIP | `sip_user` | `sip_user` |

Acesso: `tsql -S 127.0.0.1 -U sa -P 'yourStrong(!)Password'`

### OpenLDAP
| Campo | Valor |
|-------|-------|
| Admin DN | `cn=admin,dc=pen,dc=gov,dc=br` |
| Senha | `adminldap` |
| Usuario teste | senha `123456` |

---

## 17. Resolucao de Problemas

### Portas ocupadas

Antes de subir, verifique se as portas estao livres:

```bash
# Infra
sudo lsof -i :80    # HTTP
sudo lsof -i :443   # HTTPS

# Dev
sudo lsof -i :8000  # App
sudo lsof -i :1080  # MailCatcher
sudo lsof -i :8983  # Solr
sudo lsof -i :3306  # MySQL
```

Se alguma porta estiver ocupada, pare o servico ou altere o mapeamento no `envlocal.env`.

### SEI nao inicia / tela em branco

```bash
# Verificar se SEI responde
make check-sei-isalive

# Ver logs da aplicacao
make logs_app

# Ver logs do atualizador (instalacao de modulos)
make logs_app-atualizador

# Ver todos os logs
make logs
```

### Servicos orfaos apos mudanca de configuracao

```bash
# Sempre rode clear antes de alterar envlocal.env
make clear
# Agora edite envlocal.env
make run
```

### Container do banco nao inicia

```bash
# Ver logs do banco
docker logs $(docker ps -aqf "name=db" | head -1)

# Recriar volume do banco (perde dados)
make apagar_volume_banco
make criar_volume_banco
make run
```

### Recomecar do zero

```bash
cd infra
make clear
make apagar_volumes
# Edite envlocal.env se necessario
make setup
```

### Versao incompativel

```bash
# Verificar compatibilidade
make check-version-compatibility
```

O projeto valida se a versao do codigo-fonte e compativel com as imagens Docker.

### Docker Compose v1 vs v2

O projeto detecta automaticamente. Se tiver problemas:
```bash
# Verificar qual esta instalado
docker compose version    # v2
docker-compose --version  # v1
```

---

## 18. Video Tutoriais

Videos gravados pela equipe do projeto com orientacoes praticas:

| Video | Conteudo | Link |
|-------|----------|------|
| Infra Reduzida Rapidamente | Subida basica em localhost com HTTPS | [Assistir](https://www.youtube.com/watch?v=FwPp9lZiHuM) |
| Infra Completa Rapidamente | Todos os componentes, domain name, HTTPS | [Assistir](https://www.youtube.com/watch?v=MpTLtDlSVLw) |
| SqlServer ou Oracle | Como usar bancos alternativos | [Assistir](https://www.youtube.com/watch?v=IgEiR5CZEEs) |
| Organizacao do Projeto | Estrutura tecnica e anatomia da solucao | [Assistir](https://www.youtube.com/watch?v=rczbANlWVRY) |
| Customizacoes Basicas | Trocar hostname, Solr admin, scale | [Assistir](https://www.youtube.com/watch?v=HjZfryu0sco) |
| Customizacoes Completas | Todos os componentes, LDAP, orientacoes avancadas | [Assistir](https://www.youtube.com/watch?v=m5wXBPDMVQQ) |

> Recomendado assistir na ordem listada acima.

---

## 19. Dicas e Boas Praticas

1. **Comece simples.** Suba com as configuracoes padrao primeiro. Teste. Depois va customizando um parametro por vez.

2. **Sempre `make clear` antes de alterar.** Evita servicos orfaos e conflitos.

3. **Nao altere o envlocal.env inteiro de uma vez.** Altere um parametro, suba, teste, repita.

4. **Volumes sao seus dados.** `make apagar_volumes` apaga banco de dados, arquivos anexados, indices Solr. Use com cuidado.

5. **`make clear` e seguro.** Remove apenas containers e redes, nunca dados.

6. **Verifique portas antes de subir.** Portas 80 e 443 (infra) ou 8000 (dev) devem estar livres.

7. **Use `make help`.** Todos os Makefiles possuem ajuda embutida com descricao de cada comando.

8. **DNS ou /etc/hosts.** Ao usar um dominio customizado, garanta que o DNS ou `/etc/hosts` aponte para a maquina correta.

9. **Backup dos volumes.** Em ambiente de teste/treinamento, faca backup do volume do banco (`VOLUME_DB`) e dos arquivos externos (`VOLUME_ARQUIVOSEXTERNOS`).

10. **Nao e para producao.** O projeto e para DTH. Producao requer hardening de seguranca, firewall, backup e configuracoes adicionais conforme documentacao do TRF e PEN.
