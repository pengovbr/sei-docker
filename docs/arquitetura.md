# Arquitetura Técnica - SEI-Docker

## Visão Geral

O SEI-Docker implementa uma arquitetura de microsserviços baseada em containers Docker para o Sistema Eletrônico de Informações (SEI). O projeto é destinado exclusivamente a ambientes de **Desenvolvimento, Teste e Homologação (DTH)**, não sendo recomendado para produção.

---

## Diagrama de Serviços

```
                         ┌─────────────────────────┐
                         │     Usuário / Browser    │
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

| Componente | Imagem | Função |
|------------|--------|--------|
| **Traefik** | `traefik:v3.6.7` | Reverse proxy, balanceamento de carga, terminação TLS, roteamento por labels |

- Substituiu o HAProxy na versão 3.0.0
- Suporta HTTP e HTTPS com certificados auto-gerados ou customizados
- Dashboard administrativo disponível em `/traefik`
- Escalonamento horizontal transparente via Docker labels

### 2. Camada de Aplicação

| Componente | Imagem Base | Função |
|------------|-------------|--------|
| **App (SEI/SIP)** | Rocky Linux 9.3 + PHP 8.2 | Aplicação web principal |
| **App Atualizador** | Mesma imagem do App | Executa atualizações de versão e instalação de módulos |
| **App Agendador** | Extensão do App | Jobs em background via Cron, Gearman e Supervisor |
| **App Dev** | Extensão do App Base | Ambiente de desenvolvimento com XDebug |

**Stack da aplicação:**
- Apache 2.4 + mod_ssl + PHP-FPM
- PHP 8.2 com extensões: bcmath, gd, gmp, imap, intl, ldap, mbstring, pdo, memcache, memcached, gearman
- Java 1.8 (conversão de documentos)
- Locale: pt_BR.ISO-8859-1

### 3. Camada de Dados

| Componente | Versão | Porta | Observação |
|------------|--------|-------|------------|
| **MariaDB** | 10.5 | 3306 | Fork do MySQL, suporte completo |
| **MySQL** | 8.0.21 | 3306 | Banco padrão para dev |
| **PostgreSQL** | 15 | 5432 | Autenticação SCRAM-SHA-256 |
| **Oracle** | 11g XE | 1521 | Versão Express |
| **SQL Server** | 2019 | 1433 | Experimental, não para produção |

Cada banco de dados possui imagens pré-populadas com o schema do SEI para as versões 4.0, 4.1 e 5.0.

### 4. Camada de Serviços Auxiliares

| Componente | Imagem | Função |
|------------|--------|--------|
| **Memcached** | `memcached:latest` | Cache de sessões PHP e cache da aplicação |
| **Apache Solr** | 8.2.0 / 9.4.0 / 9.6.1 | Indexação e busca full-text de documentos |
| **JOD Converter** | 4.4.8 (Alpine) | Conversão de documentos via LibreOffice |
| **OpenLDAP** | osixia/openldap:1.2.2 | Autenticação e diretório de usuários |
| **MailCatcher** | schickling/mailcatcher | Captura de e-mails em ambiente de teste |

### 5. Camada de Administração (opcional)

| Interface | URL | Função |
|-----------|-----|--------|
| **Traefik Dashboard** | `/traefik` | Monitoramento do load balancer |
| **Adminer** | `/dbadmin` | Administração do banco de dados |
| **phpLDAPadmin** | `/phpldapadmin` | Administração do LDAP |
| **phpMemcachedAdmin** | `/memcachedadmin` | Administração do cache |
| **Solr Admin** | `/solr` | Administração da busca |
| **MailCatcher** | `/mailadmin` | Visualização de e-mails capturados |

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

## Fluxo de Inicialização

### Infraestrutura (infra/)

```
make setup
    │
    ├── check-version-compatibility    # Valida versão do fonte vs containers
    ├── criar_volumes                  # Cria volumes Docker persistentes
    │   ├── criar_volume_fontes        # Código-fonte do SEI
    │   ├── criar_volume_certs         # Certificados SSL
    │   ├── criar_volume_banco         # Dados do banco
    │   ├── criar_volume_arquivos_externos  # Anexos
    │   ├── criar_volume_solr          # Índices de busca
    │   ├── criar_volume_openldap      # Dados LDAP
    │   └── criar_volume_controlador_instalacao  # Estado de instalação
    │
    └── run
        ├── build_docker_compose       # Gera docker-compose.yml via envsubst
        └── docker compose up -d       # Sobe todos os serviços
            │
            ├── db                     # Banco de dados inicia
            ├── memcached              # Cache inicia
            ├── solr                   # Busca inicia
            ├── app-atualizador        # Instala/atualiza SEI e módulos
            ├── app                    # Aplicação web inicia
            ├── app-agendador          # Jobs em background iniciam
            └── balanceador            # Traefik começa a rotear
```

### Desenvolvimento (dev/)

```
make up
    │
    ├── prerequisites-up
    │   ├── env.env                    # Carrega config do banco escolhido
    │   └── check-sei-path             # Valida código-fonte existe
    │
    └── docker compose up -d
        ├── database                   # Banco com schema pré-populado
        ├── memcached                  # Cache
        ├── solr                       # Busca
        ├── jod                        # Conversão de documentos
        ├── smtp                       # MailCatcher
        └── httpd                      # App com XDebug (porta 8000)
```

---

## Comunicação entre Serviços

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

| Volume | Conteúdo | Backup Recomendado |
|--------|----------|-------------------|
| `local-storage-db` | Dados do banco de dados | Sim |
| `local-fontes-storage` | Código-fonte do SEI | Não (vem do repositório) |
| `local-certs-storage` | Certificados SSL | Sim |
| `local-arquivosexternos-storage` | Documentos anexados ao SEI | Sim |
| `local-volume-solr` | Índices de busca Solr | Não (reconstruível) |
| `local-openldap-slapd-storage` | Config LDAP | Sim (se LDAP ativo) |
| `local-openldap-db-storage` | Dados LDAP | Sim (se LDAP ativo) |
| `local-controlador-instalacao-storage` | Estado de instalação de módulos | Não (reconstruível) |

---

## Sistema de Módulos

O SEI suporta módulos opcionais que são instalados automaticamente pelo container `app-atualizador`. Cada módulo é controlado por variáveis `MODULO_*_INSTALAR` e `MODULO_*_VERSAO`.

| Módulo | Função |
|--------|--------|
| **Estatísticas** | Painel de estatísticas de uso do SEI |
| **REST / WSSEI** | API REST para integração com sistemas externos |
| **Gestão Documental** | Gestão do ciclo de vida de documentos |
| **Resposta** | Módulo de respostas a demandas |
| **Login Único** | Integração com GOV.BR (SSO) |
| **Assinatura Avançada** | Assinatura digital com ICP-Brasil e cloud PSC |
| **PEN / Barramento** | Tramitação entre órgãos via Processo Eletrônico Nacional |
| **Peticionamento** | Peticionamento eletrônico externo |
| **Protocolo Integrado** | Integração com Protocolo.GOV.BR |
| **INCOM** | Integração com Imprensa Nacional para publicações oficiais |

---

## Decisões Arquiteturais

| Decisão | Motivo |
|---------|--------|
| Traefik em vez de HAProxy (v3.0.0+) | Integração nativa com Docker labels, dashboard embutido |
| Rocky Linux 9.3 em vez de CentOS 7 | CentOS 7 EOL, Rocky é continuidade do RHEL |
| PHP 8.2 como stack principal | Compatibilidade com SEI 5.0+ |
| Volumes externos no infra | Persistência entre rebuilds, backup independente |
| envsubst + sed para docker-compose | Geração dinâmica com toggles de serviços opcionais |
| Imagens com schema pré-populado | Inicialização rápida, sem necessidade de restaurar backups |
