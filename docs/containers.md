# Containers - Referencia Tecnica

Detalhamento de todas as 44 imagens Docker do projeto SEI-Docker.

---

## Imagens Base

### base-centos7

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/base-centos/` |
| **Base** | `centos:7` |
| **Funcao** | Imagem base para containers PHP 7 (legado) |
| **Observacao** | Repositorios redirecionados para vault.centos.org (CentOS 7 EOL) |

### base-rocky93

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/base-rocky93/` |
| **Base** | `rockylinux:9.3` |
| **Funcao** | Imagem base para containers PHP 8 e servicos modernos |

---

## Containers de Aplicacao (PHP 8 - Atual)

### base-app-php8

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app-php8/app-base-php8/` |
| **Base** | `processoeletronico/base-rocky93:latest` |
| **Funcao** | Base com todas as dependencias PHP 8 |

**Pacotes instalados:**
- PHP 8.2: bcmath, gd, gmp, imap, intl, ldap, mbstring, odbc, pdo, memcache, memcached, gearman
- Apache 2.4 com mod_ssl
- Java 1.8 (OpenJDK)
- Drivers de banco: MySQL, SQL Server (ODBC), Oracle, PostgreSQL
- wkhtmltopdf, ffmpeg
- Locale: pt_BR.ISO-8859-1
- XDebug 3 (disponivel para ativacao)

### app-ci-php8

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app-php8/app-ci-php8/` |
| **Base** | `processoeletronico/base-app-php8:latest` |
| **Portas** | 80, 443 |
| **Funcao** | Container de CI/Producao com codigo SEI |

**Funcionalidades:**
- Clona modulos do SEI via Git
- Copia ConfiguracaoSEI.php e ConfiguracaoSip.php
- Entrypoint configura banco, modulos e servicos na inicializacao
- Suporta download de fontes via Git SSH ou GitHub Token

### app-ci-php8-agendador

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app-php8/app-ci-php8-agendador/` |
| **Base** | `processoeletronico/app-ci-php8:latest` |
| **Entrypoint** | `/entrypoint-agendador.sh` |
| **Funcao** | Jobs em background e tarefas agendadas |

**Servicos gerenciados (Supervisor):**
- Cron jobs: AgendamentoTarefaSEI.php, AgendamentoTarefaSip.php, limpeza de temporarios
- Gearman job server (para modulo PEN)
- Workers do PEN configurados via `MODULO_PEN_QTD_WORKER_PROC`

### app-dev-php8

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app-php8/app-dev-php8/` |
| **Base** | `processoeletronico/base-app-php8:latest` |
| **Porta** | 8000 |
| **Funcao** | Ambiente de desenvolvimento com XDebug |

**Diferenciais:**
- XDebug 3 ativado (porta 9003)
- SSL enforcement desabilitado
- Ferramentas de build instaladas
- Codigo-fonte montado via volume externo

---

## Containers de Aplicacao (PHP 7 - Legado)

### base-app

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app/app-base/` |
| **Base** | `processoeletronico/base-centos7:latest` |
| **Funcao** | Base PHP 7 (substituido por app-php8) |

### app-ci

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app/app-ci/` |
| **Base** | `processoeletronico/base-app:latest` |
| **Portas** | 80, 443 |
| **Funcao** | CI/Producao PHP 7 |

### app-ci-agendador

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app/app-ci-agendador/` |
| **Base** | `processoeletronico/app-ci:latest` |
| **Funcao** | Agendador PHP 7 |

### app-dev

| Atributo | Valor |
|----------|-------|
| **Caminho** | `containers/app/app-dev/` |
| **Base** | `processoeletronico/base-app:latest` |
| **Porta** | 8000 |
| **Funcao** | Dev PHP 7 com XDebug |

---

## Containers de Banco de Dados

### MariaDB

| Imagem | Caminho | Base | Schema |
|--------|---------|------|--------|
| `base-mariadb10.5` | `containers/databases/mariadb-base/` | `mariadb:10.5` | -- |
| `mariadb10.5-sei40` | `containers/databases/mariadb-sei40/` | `base-mariadb10.5` | SEI 4.0 |
| `mariadb10.5-sei41` | `containers/databases/mariadb-sei41/` | `base-mariadb10.5` | SEI 4.1 |
| `mariadb10.5-sei50` | `containers/databases/mariadb-sei50/` | `base-mariadb10.5` | SEI 5.0 |

**Caracteristicas:**
- Cria databases `sei` e `sip` com usuarios dedicados
- Build em dois estagios: inicializacao do schema + copia dos dados
- Schema baixado do GitHub: `spbgovbr/sei-db-ref-executivo`

### MySQL 8

| Imagem | Caminho | Base | Schema |
|--------|---------|------|--------|
| `base-mysql8` | `containers/databases/mysql8-base/` | `mysql:8.0.21` | -- |
| `mysql8-sei41` | `containers/databases/mysql8-sei41/` | `base-mysql8` | SEI 4.1 |
| `mysql8-sei50` | `containers/databases/mysql8-sei50/` | `base-mysql8` | SEI 5.0 |

### PostgreSQL 15

| Imagem | Caminho | Base | Schema |
|--------|---------|------|--------|
| `base-postgres15` | `containers/databases/postgres-base/` | `postgres:15` | -- |
| `postgres15-sei40` | `containers/databases/postgres-sei40/` | `base-postgres15` | SEI 4.0 |
| `postgres15-sei41` | `containers/databases/postgres-sei41/` | `base-postgres15` | SEI 4.1 |
| `postgres15-sei50` | `containers/databases/postgres-sei50/` | `base-postgres15` | SEI 5.0 |

**Caracteristicas:**
- Autenticacao SCRAM-SHA-256
- Build em dois estagios similar ao MariaDB

### Oracle 11g

| Imagem | Caminho | Base | Schema |
|--------|---------|------|--------|
| `base-oracle11g` | `containers/databases/oracle-base/` | `oracleinanutshell/oracle-xe-11g` | -- |
| `oracle11g-sei40` | `containers/databases/oracle-sei40/` | `base-oracle11g` | SEI 4.0 |
| `oracle11g-sei41` | `containers/databases/oracle-sei41/` | `base-oracle11g` | SEI 4.1 |
| `oracle11g-sei50` | `containers/databases/oracle-sei50/` | `base-oracle11g` | SEI 5.0 |

**Caracteristicas:**
- Importacao via dump (.dmp) com utilitario `imp`
- Character set com suporte a portugues brasileiro

### SQL Server 2019

| Imagem | Caminho | Base | Schema |
|--------|---------|------|--------|
| `base-sqlserver2019` | `containers/databases/sqlserver-base/` | `liaisonintl/mssql-server-linux:v2019` | -- |
| `sqlserver2019-sei40` | `containers/databases/sqlserver-sei40/` | `base-sqlserver2019` | SEI 4.0 |
| `sqlserver2019-sei41` | `containers/databases/sqlserver-sei41/` | `base-sqlserver2019` | SEI 4.1 |
| `sqlserver2019-sei50` | `containers/databases/sqlserver-sei50/` | `base-sqlserver2019` | SEI 5.0 |

**Caracteristicas:**
- Experimental, nao recomendado para producao
- Restauracao via backup (.bak) com `sqlcmd`
- Senha SA padrao: `yourStrong(!)Password`

---

## Containers de Busca (Solr)

| Imagem | Caminho | Base | Java | Porta |
|--------|---------|------|------|-------|
| `solr8.2.0` | `containers/solr/` | `base-centos7` | Java 8 | 8983 |
| `solr9.4.0` | `containers/solr-9.4.0/` | `base-rocky93` | Java 17 | 8983 |
| `solr9.6.1` | `containers/solr-9.6.1/` | `base-rocky93` | Java 17 | 8983 |

**Cores criados automaticamente:**
- `sei-protocolos` - Documentos de protocolo
- `sei-bases-conhecimento` - Base de conhecimento
- `sei-publicacoes` - Publicacoes

**Observacao:** Solr 9.x executa como usuario `solr` (nao-root) com diretorio de dados em `/dados`.

---

## Container de Conversao de Documentos (JOD)

| Imagem | Caminho | Base | Funcao |
|--------|---------|------|--------|
| `jod` | `containers/jod/` | `base-centos7` | JODConverter 2.2.2 (Tomcat) - legado |
| `jod4.4.8` | `containers/jod4.4.8/` | `alpine:3.21` | JODConverter 4.4.8 (standalone) - atual |

**JOD 4.4.8:**
- LibreOffice com integracao nativa
- OpenJDK 17
- Fontes: DejaVu, Noto, Terminus, Inconsolata, FontAwesome
- Heap Java: 2GB
- Executa como JAR standalone

---

## Container de Cache (Memcached)

| Imagem | Caminho | Base | Porta |
|--------|---------|------|-------|
| `memcached` | `containers/memcached/` | `memcached:latest` | 11211 |

**Usos:**
- Cache de sessoes PHP (quando `APP_MEMCACHED_SESSION=true`)
- Cache da aplicacao SEI

---

## Containers de Load Balancer

| Imagem | Caminho | Base | Portas |
|--------|---------|------|--------|
| `traefik-base` | `containers/traefik/traefik-base/` | `traefik:v3.6.7` | -- |
| `traefik` | `containers/traefik/traefik/` | `traefik-base` | 80, 443 |
| `haproxy-base` | `containers/haproxy/haproxy-base/` | (legado) | -- |
| `haproxy` | `containers/haproxy/haproxy/` | `haproxy-base` | 80, 443 |

**Traefik** e o load balancer atual (desde v3.0.0). Possui configuracao dinamica em `/etc/traefik/dynamic_conf/conf.yml`.

---

## Containers LDAP

| Imagem | Caminho | Base | Porta |
|--------|---------|------|-------|
| `openldap-base` | `containers/openldap/openldap-base/` | `osixia/openldap:1.2.2` | 389, 636 |
| `openldap` | `containers/openldap/openldap/` | `openldap-base` | 389, 636 |
| `phpldapadmin` | `containers/phpldapadmin/` | `osixia/phpldapadmin:0.9.0` | 80 |

**OpenLDAP:** Inclui LDIF de bootstrap com usuario de teste (senha: `123456`).

---

## Containers de Administracao

| Imagem | Caminho | Base | Funcao |
|--------|---------|------|--------|
| `phpmemcachedadmin-base` | `containers/phpmemcachedadmin/phpmemcachedadmin-base/` | (pre-built) | Base admin Memcached |
| `phpmemcachedadmin` | `containers/phpmemcachedadmin/phpmemcachedadmin/` | `phpmemcachedadmin-base` | Admin Memcached |
| `dbadminer` | `containers/dbadminer/` | `dockette/adminer:full` | Admin universal de banco |
| `mailcatcher` | `containers/mailcatcher/` | `schickling/mailcatcher` | Captura SMTP (porta web 1080) |

---

## Comandos de Build

```bash
cd containers

# Copiar template de configuracao
make getenv

# Editar envcontainers.env com suas configuracoes

# Build de todas as imagens
make build-conteiners

# Build de uma imagem especifica
make build-conteiner-base-rocky93
make build-conteiner-base-app-php8
make build-conteiner-app-ci-php8
make build-conteiner-mysql8-sei50

# Apagar uma imagem
make erase-conteiner-app-ci-php8

# Publicar no registry
make publish-container-app-ci-php8

# Apagar todas as imagens locais
make erase-conteiners-local
```
