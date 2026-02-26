# Arquitetura Tecnica - SEI-Docker

## Visao Geral

O SEI-Docker implementa uma arquitetura de microservicos baseada em containers Docker para o Sistema Eletronico de Informacoes (SEI). O projeto e destinado exclusivamente a ambientes de **Desenvolvimento, Teste e Homologacao (DTH)**, nao sendo recomendado para producao.

---

## Diagrama de Servicos

```
                         ┌─────────────────────────┐
                         │     Usuario / Browser    │
                         └────────────┬────────────┘
                                      │
                              ┌───────▼───────┐
                              │    Traefik     │
                              │  (balanceador) │
                              │   :80 / :443   │
                              └───────┬───────┘
                                      │
                    ┌─────────────────┼─────────────────┐
                    │                 │                   │
              ┌─────▼─────┐   ┌──────▼──────┐   ┌───────▼───────┐
              │   App #1   │   │   App #2    │   │   App #N      │
              │  (SEI/SIP) │   │  (SEI/SIP)  │   │  (SEI/SIP)    │
              │  PHP 8.2   │   │  PHP 8.2    │   │  PHP 8.2      │
              └─────┬──────┘   └──────┬──────┘   └───────┬───────┘
                    │                 │                   │
       ┌────────────┼─────────────────┼───────────────────┤
       │            │                 │                   │
 ┌─────▼─────┐ ┌───▼────┐ ┌────▼────┐ ┌────▼────┐ ┌─────▼──────┐
 │   Banco   │ │Memcached│ │  Solr   │ │  JOD    │ │   Mail     │
 │  de Dados │ │ :11211  │ │ :8983   │ │ (conv.) │ │  Catcher   │
 └───────────┘ └────────┘ └─────────┘ └─────────┘ └────────────┘

       ┌────────────┐  ┌───────────────┐
       │  OpenLDAP   │  │  Agendador    │
       │  :389/:636  │  │ (Cron/Gearman)│
       └────────────┘  └───────────────┘
```

---

## Camadas da Arquitetura

### 1. Camada de Entrada (Load Balancer)

| Componente | Imagem | Funcao |
|------------|--------|--------|
| **Traefik** | `traefik:v3.6.7` | Reverse proxy, balanceamento de carga, terminacao TLS, roteamento por labels |

- Substituiu o HAProxy na versao 3.0.0
- Suporta HTTP e HTTPS com certificados auto-gerados ou customizados
- Dashboard administrativo disponivel em `/traefik`
- Escalonamento horizontal transparente via Docker labels

### 2. Camada de Aplicacao

| Componente | Imagem Base | Funcao |
|------------|-------------|--------|
| **App (SEI/SIP)** | Rocky Linux 9.3 + PHP 8.2 | Aplicacao web principal |
| **App Atualizador** | Mesma imagem do App | Executa atualizacoes de versao e instalacao de modulos |
| **App Agendador** | Extensao do App | Jobs em background via Cron, Gearman e Supervisor |
| **App Dev** | Extensao do App Base | Ambiente de desenvolvimento com XDebug |

**Stack da aplicacao:**
- Apache 2.4 + mod_ssl + PHP-FPM
- PHP 8.2 com extensoes: bcmath, gd, gmp, imap, intl, ldap, mbstring, pdo, memcache, memcached, gearman
- Java 1.8 (conversao de documentos)
- Locale: pt_BR.ISO-8859-1

### 3. Camada de Dados

| Componente | Versao | Porta | Observacao |
|------------|--------|-------|------------|
| **MariaDB** | 10.5 | 3306 | Fork do MySQL, suporte completo |
| **MySQL** | 8.0.21 | 3306 | Banco padrao para dev |
| **PostgreSQL** | 15 | 5432 | Autenticacao SCRAM-SHA-256 |
| **Oracle** | 11g XE | 1521 | Versao Express |
| **SQL Server** | 2019 | 1433 | Experimental, nao para producao |

Cada banco de dados possui imagens pre-populadas com o schema do SEI para as versoes 4.0, 4.1 e 5.0.

### 4. Camada de Servicos Auxiliares

| Componente | Imagem | Funcao |
|------------|--------|--------|
| **Memcached** | `memcached:latest` | Cache de sessoes PHP e cache da aplicacao |
| **Apache Solr** | 8.2.0 / 9.4.0 / 9.6.1 | Indexacao e busca full-text de documentos |
| **JOD Converter** | 4.4.8 (Alpine) | Conversao de documentos via LibreOffice |
| **OpenLDAP** | osixia/openldap:1.2.2 | Autenticacao e diretorio de usuarios |
| **MailCatcher** | schickling/mailcatcher | Captura de e-mails em ambiente de teste |

### 5. Camada de Administracao (opcional)

| Interface | URL | Funcao |
|-----------|-----|--------|
| **Traefik Dashboard** | `/traefik` | Monitoramento do load balancer |
| **Adminer** | `/dbadmin` | Administracao do banco de dados |
| **phpLDAPadmin** | `/phpldapadmin` | Administracao do LDAP |
| **phpMemcachedAdmin** | `/memcachedadmin` | Administracao do cache |
| **Solr Admin** | `/solr` | Administracao da busca |
| **MailCatcher** | `/mailadmin` | Visualizacao de e-mails capturados |

---

## Hierarquia de Imagens Docker

```
centos:7                          rockylinux:9.3
    └── base-centos7                  └── base-rocky93
        ├── base-app (PHP 7)              ├── base-app-php8 (PHP 8)
        │   ├── app-ci                    │   ├── app-ci-php8
        │   │   └── app-ci-agendador      │   │   └── app-ci-php8-agendador
        │   └── app-dev                   │   └── app-dev-php8
        ├── solr8.2.0                     ├── solr9.4.0
        └── jod                           └── solr9.6.1

mysql:8.0.21          mariadb:10.5          postgres:15
    └── base-mysql8       └── base-mariadb10.5  └── base-postgres15
        ├── mysql8-sei41      ├── mariadb-sei40     ├── postgres-sei40
        └── mysql8-sei50      ├── mariadb-sei41     ├── postgres-sei41
                              └── mariadb-sei50     └── postgres-sei50

oracleinanutshell/oracle-xe-11g       liaisonintl/mssql-server-linux:v2019
    └── base-oracle11g                    └── base-sqlserver2019
        ├── oracle-sei40                      ├── sqlserver-sei40
        ├── oracle-sei41                      ├── sqlserver-sei41
        └── oracle-sei50                      └── sqlserver-sei50

traefik:v3.6.7                 osixia/openldap:1.2.2
    └── traefik-base               └── openldap-base
        └── traefik                    └── openldap
```

---

## Fluxo de Inicializacao

### Infraestrutura (infra/)

```
make setup
    │
    ├── check-version-compatibility    # Valida versao do fonte vs containers
    ├── criar_volumes                  # Cria volumes Docker persistentes
    │   ├── criar_volume_fontes        # Codigo-fonte do SEI
    │   ├── criar_volume_certs         # Certificados SSL
    │   ├── criar_volume_banco         # Dados do banco
    │   ├── criar_volume_arquivos_externos  # Anexos
    │   ├── criar_volume_solr          # Indices de busca
    │   ├── criar_volume_openldap      # Dados LDAP
    │   └── criar_volume_controlador_instalacao  # Estado de instalacao
    │
    └── run
        ├── build_docker_compose       # Gera docker-compose.yml via envsubst
        └── docker compose up -d       # Sobe todos os servicos
            │
            ├── db                     # Banco de dados inicia
            ├── memcached              # Cache inicia
            ├── solr                   # Busca inicia
            ├── app-atualizador        # Instala/atualiza SEI e modulos
            ├── app                    # Aplicacao web inicia
            ├── app-agendador          # Jobs em background iniciam
            └── balanceador            # Traefik comeca a rotear
```

### Desenvolvimento (dev/)

```
make up
    │
    ├── prerequisites-up
    │   ├── env.env                    # Carrega config do banco escolhido
    │   └── check-sei-path             # Valida codigo-fonte existe
    │
    └── docker compose up -d
        ├── database                   # Banco com schema pre-populado
        ├── memcached                  # Cache
        ├── solr                       # Busca
        ├── jod                        # Conversao de documentos
        ├── smtp                       # MailCatcher
        └── httpd                      # App com XDebug (porta 8000)
```

---

## Comunicacao entre Servicos

| De | Para | Protocolo | Porta |
|----|------|-----------|-------|
| Traefik | App | HTTP | 80/443 |
| App | Banco de Dados | TCP | 3306/5432/1521/1433 |
| App | Memcached | TCP | 11211 |
| App | Solr | HTTP | 8983 |
| App | JOD | HTTP | 8080 |
| App | OpenLDAP | LDAP | 389/636 |
| App | Mail | SMTP | 25 |
| Agendador | Banco de Dados | TCP | 3306/5432/1521/1433 |
| Agendador | Gearman | TCP | 4730 |
| Agendador | Memcached | TCP | 11211 |

---

## Volumes Persistentes

| Volume | Conteudo | Backup Recomendado |
|--------|----------|-------------------|
| `local-storage-db` | Dados do banco de dados | Sim |
| `local-fontes-storage` | Codigo-fonte do SEI | Nao (vem do repositorio) |
| `local-certs-storage` | Certificados SSL | Sim |
| `local-arquivosexternos-storage` | Documentos anexados ao SEI | Sim |
| `local-volume-solr` | Indices de busca Solr | Nao (reconstruivel) |
| `local-openldap-slapd-storage` | Config LDAP | Sim (se LDAP ativo) |
| `local-openldap-db-storage` | Dados LDAP | Sim (se LDAP ativo) |
| `local-controlador-instalacao-storage` | Estado de instalacao de modulos | Nao (reconstruivel) |

---

## Sistema de Modulos

O SEI suporta modulos opcionais que sao instalados automaticamente pelo container `app-atualizador`. Cada modulo e controlado por variaveis `MODULO_*_INSTALAR` e `MODULO_*_VERSAO`.

| Modulo | Funcao |
|--------|--------|
| **Estatisticas** | Painel de estatisticas de uso do SEI |
| **REST / WSSEI** | API REST para integracao com sistemas externos |
| **Gestao Documental** | Gestao do ciclo de vida de documentos |
| **Resposta** | Modulo de respostas a demandas |
| **Login Unico** | Integracao com GOV.BR (SSO) |
| **Assinatura Avancada** | Assinatura digital com ICP-Brasil e cloud PSC |
| **PEN / Barramento** | Tramitacao entre orgaos via Processo Eletronico Nacional |
| **Peticionamento** | Peticionamento eletronico externo |
| **Protocolo Integrado** | Integracao com Protocolo.GOV.BR |
| **INCOM** | Integracao com Imprensa Nacional para publicacoes oficiais |

---

## Decisoes Arquiteturais

| Decisao | Motivo |
|---------|--------|
| Traefik em vez de HAProxy (v3.0.0+) | Integracao nativa com Docker labels, dashboard embutido |
| Rocky Linux 9.3 em vez de CentOS 7 | CentOS 7 EOL, Rocky e continuidade do RHEL |
| PHP 8.2 como stack principal | Compatibilidade com SEI 5.0+ |
| Volumes externos no infra | Persistencia entre rebuilds, backup independente |
| envsubst + sed para docker-compose | Geracao dinamica com toggles de servicos opcionais |
| Imagens com schema pre-populado | Inicializacao rapida, sem necessidade de restaurar backups |
