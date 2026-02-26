# Testes - Infraestrutura de Testes

Documentacao tecnica sobre as suites de teste do projeto SEI-Docker.

---

## Visao Geral

O projeto possui **3 niveis de teste**, cada um com escopo e objetivos diferentes:

| Nivel | Caminho | Escopo |
|-------|---------|--------|
| **Containers** | `containers/tests/` | Valida build, push e pull de todas as 44 imagens |
| **Infraestrutura** | `infra/tests/` | Testa deploy completo em 16 combinacoes (4 modalidades x 4 bancos) |
| **Desenvolvimento** | `dev/tests/` | Testes funcionais com Selenium no ambiente dev |

---

## 1. Testes de Containers

**Caminho:** `containers/tests/`
**Comando:** `make test-containers`

### O que testa

Para cada uma das 44 imagens Docker:

1. **Erase** - Apaga imagem local (se existir)
2. **Build** - Reconstroi a imagem
3. **Push** - Publica no registry
4. **Re-erase** - Apaga a imagem local novamente
5. **Pull** - Baixa do registry
6. **Final erase** - Limpeza final

### Imagens testadas

**Bases:** base-centos7, base-rocky93

**Bancos:** base-mariadb10.5, base-mysql8, base-sqlserver2019, base-oracle11g, base-postgres15, mariadb10.5-sei40/41/50, mysql8-sei41/50, sqlserver2019-sei40/41/50, oracle11g-sei40/41/50, postgres15-sei40/41/50

**Aplicacao:** base-app, base-app-php8, app-dev, app-dev-php8, app-ci, app-ci-php8, app-ci-agendador, app-ci-php8-agendador

**Servicos:** memcached, jod, jod4.4.8, solr8.2.0, solr9.4.0, solr9.6.1, traefik-base, traefik, haproxy-base, haproxy, openldap-base, openldap, phpmemcachedadmin-base, phpmemcachedadmin, phpldapadmin, dbadminer, mailcatcher

---

## 2. Testes de Infraestrutura

**Caminho:** `infra/tests/`
**Makefile:** `infra/tests/Makefile`

### Matriz de Testes

Os testes combinam **modalidades de deploy** com **bancos de dados**:

| | MySQL | SQL Server | Oracle | PostgreSQL |
|---|---|---|---|---|
| **Reduzida** | X | X | X | X |
| **Default** | X | X | X | X |
| **Completa** | X | X | X | X |
| **HTTP** | X | X | X | X |

**Total:** 16 combinacoes

### Modalidades

| Modalidade | Descricao |
|------------|-----------|
| **Reduzida** | Sem load balancer, URL customizada, HTTPS |
| **Default** | Com load balancer, localhost |
| **Completa** | Todos os servicos opcionais habilitados, HTTPS |
| **HTTP** | Variantes HTTP (sem TLS) |

### Comandos

```bash
cd infra/tests

# Rodar suite completa (todas as 16 combinacoes)
make test_lineup_completa

# Rodar por modalidade
make test_ambientes_reduzida
make test_ambientes_default
make test_ambientes_completa
make test_ambientes_full

# Rodar combinacao especifica
make MODALIDADE=completa BANCO=oracle test_ambiente
make MODALIDADE=default BANCO=mysql test_ambiente
make MODALIDADE=reduzida BANCO=postgres test_ambiente
```

### Arquivos de Configuracao de Teste

| Arquivo | Modalidade |
|---------|------------|
| `test-envlocal-reduzida.env` | Deploy reduzido |
| `test-envlocal-http.env` | Deploy HTTP |
| `test-envlocal-completa.env` | Deploy completo |
| (padrao do infra) | Deploy default |

### Validacoes Executadas

Para cada combinacao, o teste executa:

1. **Criacao de volumes** - Verifica se todos os volumes foram criados
2. **Deploy dos servicos** - `make setup` ou `make run`
3. **Health check do SEI** - Acessa tela de login via `curl`
4. **Verificacao do balanceador** - Testa dashboard Traefik (se presente)
5. **Verificacao de componentes** - JOD, admin UIs (se presentes)
6. **Escalonamento** - Escala app de 1 -> 2 -> 3 -> 1 instancias
7. **Testes Selenium** - Workflow funcional completo
8. **Limpeza** - `make clear && make apagar_volumes`

---

## 3. Testes Selenium

**Caminho:** `infra/tests/Selenium/PythonExported/`
**Arquivo principal:** `test_suiteBasics.py`

### Workflow Testado

O teste Selenium simula um usuario completo:

1. **Login** - Acessa SEI e faz login com credenciais
2. **Validacao de modulos** - Verifica se modulos instalados estao disponiveis
3. **Criacao de processo** - Cria um novo processo eletronico
4. **Documento com anexo** - Cria documento e anexa arquivo
5. **Documento interno** - Cria documento interno no processo
6. **Logout** - Realiza logout e verifica redirecionamento

### Infraestrutura Selenium

- **Driver:** Chrome headless via Docker (`selenium/standalone-chrome`)
- **Rede:** Docker network ou host networking
- **Tentativas:** Ate 3 retentativas por teste
- **Relatorio:** XML JUnit (`resultado.xml`)

### Execucao Manual

```bash
cd infra/tests

# Via Docker (recomendado)
docker run --rm \
  --network=host \
  -v $(pwd)/Selenium:/tests \
  selenium/standalone-chrome \
  python3 /tests/PythonExported/test_suiteBasics.py

# Variaveis de ambiente para o teste
export SEI_URL="https://localhost/sei"
export SEI_USUARIO="teste"
export SEI_SENHA="teste"
```

---

## 4. Testes de Desenvolvimento

**Caminho:** `dev/tests/`
**Comando:** `make tests-all-bases`

### O que testa

Executa testes funcionais Selenium em todas as combinacoes de banco de dados no ambiente de desenvolvimento.

### Restricoes

- Funciona apenas em **Linux** (usa Docker host networking)
- Requer `localhost` como URL do SEI
- Codigo-fonte deve estar disponivel em `SEI_PATH`

---

## Deteccao de Docker Compose

Todos os Makefiles detectam automaticamente a versao do Docker Compose:

```makefile
# Detecta "docker compose" (v2) ou "docker-compose" (v1)
DC := $(shell docker compose version 2>/dev/null && echo "docker compose" || echo "docker-compose")
```

---

## CI/CD

### Jenkins

**Caminho:** `infra/jenkins/`
**Status:** Placeholder (`comminganytime.txt`)

A integracao com Jenkins e prevista mas nao implementada. Atualmente o CI/CD e feito via Makefiles.

### Pipeline de CI Recomendado

```
1. Build de imagens base
   make build-conteiner-base-centos
   make build-conteiner-base-rocky93

2. Build de imagens de servico
   make build-conteiners

3. Teste de containers
   cd containers/tests && make test-containers

4. Deploy de teste
   cd infra && make setup

5. Testes de integracao
   cd infra/tests && make test_lineup_completa

6. Publicacao
   cd containers && make publish-containers
```
