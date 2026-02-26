# Documentacao Tecnica - SEI-Docker

Documentacao tecnica completa do projeto **SEI-Docker**, solucao de Infraestrutura como Codigo para provisionamento do Sistema Eletronico de Informacoes (SEI) em ambientes de Desenvolvimento, Teste e Homologacao (DTH).

**Versao do projeto:** 3.6.11

---

## Indice

| Documento | Descricao |
|-----------|-----------|
| [Guia de Uso](guia-de-uso.md) | **Comece aqui!** Guia completo de instalacao, configuracao e uso (dev + infra + containers) |
| [Arquitetura](arquitetura.md) | Visao geral da arquitetura, camadas, fluxo de dados e diagrama de servicos |
| [Containers](containers.md) | Detalhamento tecnico de cada imagem Docker: base, aplicacao, banco de dados, servicos auxiliares |
| [Variaveis de Ambiente](variaveis-ambiente.md) | Referencia completa das 230+ variaveis de ambiente organizadas por categoria |
| [Orquestracao e Deploy](orquestracao.md) | Docker Compose, Kubernetes, Rancher Cattle, Makefiles e automacao |
| [Ambiente de Desenvolvimento](desenvolvimento.md) | Configuracao do ambiente dev com XDebug, montagem de fontes e debug |
| [Testes](testes.md) | Infraestrutura de testes: containers, infraestrutura, Selenium e CI/CD |

---

## Estrutura do Projeto

```
sei-docker/
├── containers/                  # Receitas de build das imagens Docker (44 imagens)
│   ├── Makefile                 # Automacao de build/push/erase
│   ├── envcontainers.env.modelo # Template de configuracao de build
│   ├── app/                     # Containers da aplicacao PHP 7 (legado)
│   ├── app-php8/                # Containers da aplicacao PHP 8 (atual)
│   ├── databases/               # Containers de banco de dados
│   │   ├── mariadb-*/           # MariaDB 10.5 (SEI 4.0, 4.1, 5.0)
│   │   ├── mysql8-*/            # MySQL 8 (SEI 4.1, 5.0)
│   │   ├── oracle-*/            # Oracle 11g (SEI 4.0, 4.1, 5.0)
│   │   ├── postgres-*/          # PostgreSQL 15 (SEI 4.0, 4.1, 5.0)
│   │   └── sqlserver-*/         # SQL Server 2019 (SEI 4.0, 4.1, 5.0)
│   ├── solr*/                   # Apache Solr (8.2, 9.4, 9.6)
│   ├── jod*/                    # JOD Converter (legado + 4.4.8)
│   ├── traefik/                 # Load balancer Traefik
│   ├── openldap/                # Servico de diretorio LDAP
│   ├── memcached/               # Cache
│   ├── mailcatcher/             # Captura de e-mails
│   └── tests/                   # Testes dos containers
│
├── dev/                         # Ambiente de desenvolvimento
│   ├── Makefile                 # Automacao dev (up, down, config, update)
│   ├── docker-compose.yml       # Orquestracao simplificada
│   ├── envs/                    # Templates de ambiente por banco/versao
│   └── tests/                   # Testes funcionais (Selenium)
│
├── infra/                       # Infraestrutura completa (DTH)
│   ├── Makefile                 # Automacao completa (setup, run, scale, k8s)
│   ├── envlocal.env             # Configuracao principal (100+ variaveis)
│   ├── envlocal-example-*.env   # Exemplos por banco/versao
│   ├── orquestrators/           # Templates de orquestracao
│   │   ├── docker-compose/      # Docker Compose
│   │   ├── rancher-kubernetes/  # Kubernetes
│   │   └── rancher-cattle/      # Rancher Cattle (legado)
│   ├── tests/                   # Testes de integracao
│   ├── docs/                    # Documentacao original do infra
│   └── jenkins/                 # Placeholder CI/CD
│
└── docs/                        # Documentacao tecnica (este diretorio)
```

---

## Requisitos

- Docker Engine 20+
- Docker Compose v2+ (ou v1 com hifen)
- Codigo-fonte do SEI (obtido separadamente)
- GNU Make
- curl (para health checks)

---

## Links Rapidos

- **Subir ambiente dev:** `cd dev && make up`
- **Subir infraestrutura:** `cd infra && make setup`
- **Build de containers:** `cd containers && make build-conteiners`
- **Rodar testes:** `cd infra/tests && make test_lineup_completa`
