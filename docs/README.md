# Documentação Técnica - SEI-Docker

Documentação técnica completa do projeto **SEI-Docker**, solução de Infraestrutura como Código para provisionamento do Sistema Eletrônico de Informações (SEI) em ambientes de Desenvolvimento, Teste e Homologação (DTH).

**Versão do projeto:** 3.6.11

---

## Índice

| Documento | Descrição |
|-----------|-----------|
| [Guia de Uso](guia-de-uso.md) | **Comece aqui!** Guia completo de instalação, configuração e uso (dev + infra + containers) |
| [Arquitetura](arquitetura.md) | Visão geral da arquitetura, camadas, fluxo de dados e diagrama de serviços |
| [Containers](containers.md) | Detalhamento técnico de cada imagem Docker: base, aplicação, banco de dados, serviços auxiliares |
| [Variáveis de Ambiente](variaveis-ambiente.md) | Referência completa das 230+ variáveis de ambiente organizadas por categoria |
| [Orquestração e Deploy](orquestracao.md) | Docker Compose, Kubernetes, Rancher Cattle, Makefiles e automação |
| [Ambiente de Desenvolvimento](desenvolvimento.md) | Configuração do ambiente dev com XDebug, montagem de fontes e debug |
| [Testes](testes.md) | Infraestrutura de testes: containers, infraestrutura, Selenium e CI/CD |

---

## Estrutura do Projeto

```
sei-docker/
├── containers/                  # Receitas de build das imagens Docker (44 imagens)
│   ├── Makefile                 # Automação de build/push/erase
│   ├── envcontainers.env.modelo # Template de configuração de build
│   ├── app/                     # Containers da aplicação PHP 7 (legado)
│   ├── app-php8/                # Containers da aplicação PHP 8 (atual)
│   ├── databases/               # Containers de banco de dados
│   │   ├── mariadb-*/           # MariaDB 10.5 (SEI 4.0, 4.1, 5.0)
│   │   ├── mysql8-*/            # MySQL 8 (SEI 4.1, 5.0)
│   │   ├── oracle-*/            # Oracle 11g (SEI 4.0, 4.1, 5.0)
│   │   ├── postgres-*/          # PostgreSQL 15 (SEI 4.0, 4.1, 5.0)
│   │   └── sqlserver-*/         # SQL Server 2019 (SEI 4.0, 4.1, 5.0)
│   ├── solr*/                   # Apache Solr (8.2, 9.4, 9.6)
│   ├── jod*/                    # JOD Converter (legado + 4.4.8)
│   ├── traefik/                 # Load balancer Traefik
│   ├── openldap/                # Serviço de diretório LDAP
│   ├── memcached/               # Cache
│   ├── mailcatcher/             # Captura de e-mails
│   └── tests/                   # Testes dos containers
│
├── dev/                         # Ambiente de desenvolvimento
│   ├── Makefile                 # Automação dev (up, down, config, update)
│   ├── docker-compose.yml       # Orquestração simplificada
│   ├── envs/                    # Templates de ambiente por banco/versão
│   └── tests/                   # Testes funcionais (Selenium)
│
├── infra/                       # Infraestrutura completa (DTH)
│   ├── Makefile                 # Automação completa (setup, run, scale, k8s)
│   ├── envlocal.env             # Configuração principal (100+ variáveis)
│   ├── envlocal-example-*.env   # Exemplos por banco/versão
│   ├── orquestrators/           # Templates de orquestração
│   │   ├── docker-compose/      # Docker Compose
│   │   ├── rancher-kubernetes/  # Kubernetes
│   │   └── rancher-cattle/      # Rancher Cattle (legado)
│   ├── tests/                   # Testes de integração
│   ├── docs/                    # Documentação original do infra
│   └── jenkins/                 # Placeholder CI/CD
│
└── docs/                        # Documentação técnica (este diretório)
```

---

## Requisitos

- Docker Engine 20+
- Docker Compose v2+ (ou v1 com hífen)
- Código-fonte do SEI (obtido separadamente)
- GNU Make
- curl (para health checks)

---

## Links Rápidos

- **Subir ambiente dev:** `cd dev && make up`
- **Subir infraestrutura:** `cd infra && make setup`
- **Build de containers:** `cd containers && make build-conteiners`
- **Rodar testes:** `cd infra/tests && make test_lineup_completa`
