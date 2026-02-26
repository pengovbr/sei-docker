# Orquestracao e Deploy

Guia tecnico sobre as opcoes de deploy, automacao via Makefiles e orquestracao com Docker Compose e Kubernetes.

---

## Plataformas Suportadas

| Plataforma | Status | Caminho |
|------------|--------|---------|
| **Docker Compose** | Ativo (principal) | `infra/orquestrators/docker-compose/` |
| **Kubernetes** | Ativo | `infra/orquestrators/rancher-kubernetes/` |
| **Rancher Cattle** | Legado | `infra/orquestrators/rancher-cattle/` |

---

## Docker Compose (Infraestrutura)

### Geracao Dinamica do docker-compose.yml

O arquivo `docker-compose.yml` e gerado dinamicamente a partir de um template. O processo utiliza `envsubst` para substituir variaveis e `sed` para habilitar/desabilitar servicos opcionais.

**Template:** `infra/orquestrators/docker-compose/docker-compose-template.yml`

**Fluxo de geracao:**

```
envlocal.env  ──→  envsubst  ──→  sed (toggles)  ──→  docker-compose.yml
```

**Toggles de servicos (via `sed`):**

| Tag no Template | Variavel de Controle | Servico |
|-----------------|---------------------|---------|
| `#serviceldap` | `OPENLDAP_PRESENTE` | OpenLDAP + phpLDAPadmin |
| `#servicejod` | `JOD_PRESENTE` | JOD Converter |
| `#servicemail` | `MAIL_CATCHER_PRESENTE` | MailCatcher |
| `#servicememcachedadmin` | `MEMCACHEDADMIN_PRESENTE` | phpMemcachedAdmin |
| `#servicedbadmin` | `DBADMIN_PRESENTE` | Adminer |
| `#servicebal` | `BALANCEADOR_PRESENTE` | Traefik |
| `#app-traefik` | `BALANCEADOR_PRESENTE` | Labels Traefik no app |

Quando a variavel e `true`, o `sed` remove o `#tag` do inicio das linhas correspondentes, ativando o servico.

### Servicos Ativos (padrao)

| Servico | Imagem | Funcao |
|---------|--------|--------|
| `storage-app` | `busybox:latest` | Sidecar para volumes |
| `storage-certs` | `busybox:latest` | Sidecar para certificados |
| `memcached` | `${DOCKER_IMAGE_MEMCACHED}` | Cache |
| `db` | `${DOCKER_IMAGE_BD}` | Banco de dados |
| `solr` | `${DOCKER_IMAGE_SOLR}` | Busca full-text |
| `app-atualizador` | `${DOCKER_IMAGE_APP}` | Instalacao/atualizacao |
| `app-agendador` | `${DOCKER_IMAGE_APP_AGENDADOR}` | Jobs em background |
| `app` | `${DOCKER_IMAGE_APP}` | Aplicacao web |

### Servicos Opcionais

| Servico | Toggle | URL de Acesso |
|---------|--------|---------------|
| `balanceador` | `BALANCEADOR_PRESENTE=true` | `:80` / `:443` |
| `jod` | `JOD_PRESENTE=true` | interno |
| `openldap` + `ldapadmin` | `OPENLDAP_PRESENTE=true` | `/phpldapadmin` |
| `mail` | `MAIL_CATCHER_PRESENTE=true` | `/mailadmin` |
| `memcachedadmin` | `MEMCACHEDADMIN_PRESENTE=true` | `/memcachedadmin` |
| `dbadmin` | `DBADMIN_PRESENTE=true` | `/dbadmin` |

---

## Makefile de Infraestrutura

**Arquivo:** `infra/Makefile`

### Comandos Principais

```bash
# Ver todos os comandos disponiveis
make help

# Setup completo (cria volumes + sobe servicos)
make setup

# Apenas construir docker-compose.yml
make build_docker_compose

# Subir servicos (build + up -d)
make run

# Escalar aplicacao (padrao 2 nos)
make scale
make scale qtd=3

# Parar e remover containers
make stop

# Parar sem remover volumes
make clear

# Apagar TUDO (volumes inclusos - DESTRUTIVO)
make apagar_volumes
```

### Gerenciamento de Volumes

```bash
# Criar todos os volumes
make criar_volumes

# Criar volumes individuais
make criar_volume_fontes
make criar_volume_certs
make criar_volume_banco
make criar_volume_arquivos_externos
make criar_volume_solr
make criar_volume_openldap
make criar_volume_controlador_instalacao

# Apagar volumes individuais
make apagar_volume_fontes
make apagar_volume_banco
make apagar_volume_solr
```

### Logs

```bash
make logs                  # Todos os servicos
make logs_app              # Aplicacao
make logs_app-atualizador  # Atualizador
make logs_balanceador      # Traefik
make logs_openldap         # OpenLDAP
make logs_solr             # Solr
```

### Health Checks

```bash
# Verifica se SEI responde com tela de login
make check-sei-isalive

# Verifica compatibilidade de versao fonte vs containers
make check-version-compatibility

# Verifica se fontes estao posicionados
make check-fontes-posicionado
```

---

## Kubernetes

### Geracao de Manifests

Os manifests Kubernetes sao gerados a partir de templates usando `envsubst`, similar ao docker-compose.

**Templates:** `infra/orquestrators/rancher-kubernetes/templates/`

| Template | Conteudo |
|----------|----------|
| `deploys-svc-template.yaml` | Deployments e Services |
| `statefullsets-template.yaml` | StatefulSets |
| `configmaps-template.yaml` | ConfigMaps |
| `secrets-template.yaml` | Secrets (credenciais, certificados) |
| `pvc-template.yaml` | PersistentVolumeClaims |
| `jobs-template.yaml` | Jobs (one-time) |
| `ingress-template.yaml` | Ingress rules |

**Saida:** `infra/orquestrators/rancher-kubernetes/topublish/*.yaml`

### Comandos Kubernetes

```bash
# Gerar manifests a partir dos templates
make kubernetes_montar_yaml

# Aplicar no cluster
make kubernetes_apply

# Remover do cluster
make kubernetes_delete

# Verificar deploy especifico
make kubernetes_check_deploy_generic KUBE_DEPLOY_NAME=app
```

### Configuracao Kubernetes

As variaveis `KUBERNETES_*` controlam:
- **Namespace:** `KUBERNETES_NAMESPACE=seins`
- **Storage class:** `KUBERNETES_PVC_STORAGECLASS=nfs-client`
- **Recursos:** Limites e requests de CPU/memoria por servico
- **Timeout:** 180s para verificacao de deploy

---

## Makefile de Containers (Build)

**Arquivo:** `containers/Makefile`

### Setup Inicial

```bash
cd containers

# Copiar template de configuracao
make getenv

# Editar envcontainers.env
vi envcontainers.env
```

### Build de Imagens

```bash
# Build de TODAS as imagens (ordem correta de dependencias)
make build-conteiners

# Build individual (exemplos)
make build-conteiner-base-rocky93
make build-conteiner-base-app-php8
make build-conteiner-app-ci-php8
make build-conteiner-app-ci-php8-agendador
make build-conteiner-mysql8-sei50
make build-conteiner-solr-9.6.1
make build-conteiner-traefik

# Build generico (parametrizado)
make build-conteiner-generic \
  IMAGEMTMP=minha-imagem \
  IMAGEMTMP_VERSAO=1.0.0 \
  IMAGEMTMP_PATH=caminho/do/dockerfile
```

### Publicacao no Registry

```bash
# Publicar todas as imagens
make publish-containers

# Publicar individual
make publish-container-app-ci-php8
make publish-container-mysql8-sei50
```

Cada publicacao envia a tag versionada e `:latest` (exceto quando versao e "test").

### Limpeza

```bash
# Apagar TODAS as imagens locais
make erase-conteiners-local

# Apagar individual
make erase-conteiner-app-ci-php8
make erase-conteiner-mysql8-sei50
```

---

## Ordem de Build (Dependencias)

A ordem correta de build respeita a hierarquia de imagens:

```
1. Imagens base do SO
   ├── base-centos7
   └── base-rocky93

2. Imagens base de servico
   ├── base-mariadb10.5
   ├── base-mysql8
   ├── base-sqlserver2019
   ├── base-oracle11g
   ├── base-postgres15
   ├── base-app (depende de base-centos7)
   ├── base-app-php8 (depende de base-rocky93)
   ├── traefik-base
   ├── haproxy-base
   └── openldap-base

3. Imagens de banco com schema
   ├── mariadb10.5-sei40/41/50 (depende de base-mariadb10.5)
   ├── mysql8-sei41/50 (depende de base-mysql8)
   ├── sqlserver2019-sei40/41/50 (depende de base-sqlserver2019)
   ├── oracle11g-sei40/41/50 (depende de base-oracle11g)
   └── postgres15-sei40/41/50 (depende de base-postgres15)

4. Imagens da aplicacao
   ├── app-ci (depende de base-app)
   ├── app-ci-php8 (depende de base-app-php8)
   ├── app-dev (depende de base-app)
   └── app-dev-php8 (depende de base-app-php8)

5. Imagens do agendador
   ├── app-ci-agendador (depende de app-ci)
   └── app-ci-php8-agendador (depende de app-ci-php8)

6. Demais servicos
   ├── solr8.2.0 (depende de base-centos7)
   ├── solr9.4.0, solr9.6.1 (depende de base-rocky93)
   ├── jod (depende de base-centos7)
   ├── jod4.4.8 (alpine, sem dependencia)
   ├── traefik (depende de traefik-base)
   ├── openldap (depende de openldap-base)
   ├── memcached (sem dependencia)
   └── mailcatcher (sem dependencia)
```

---

## Modalidades de Deploy (Infra)

| Modalidade | Balanceador | HTTPS | Servicos Opcionais | Uso |
|------------|-------------|-------|---------------------|-----|
| **Reduzida** | Nao | Sim | Nenhum | Teste minimo |
| **Default** | Sim | Sim | JOD | Teste padrao |
| **Completa** | Sim | Sim | Todos (LDAP, Mail, DB Admin, etc) | Teste completo |
| **HTTP** | Variavel | Nao | Variavel | Sem TLS |

Configuracao via variaveis booleanas `*_PRESENTE` no `envlocal.env`.
