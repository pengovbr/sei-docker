# Orquestração e Deploy

Guia técnico sobre as opções de deploy, automação via Makefiles e orquestração com Docker Compose e Kubernetes.

---

## Plataformas Suportadas

| Plataforma | Status | Caminho |
|------------|--------|---------|
| **Docker Compose** | Ativo (principal) | `infra/orquestrators/docker-compose/` |
| **Kubernetes** | Ativo | `infra/orquestrators/rancher-kubernetes/` |
| **Rancher Cattle** | Legado | `infra/orquestrators/rancher-cattle/` |

---

## Docker Compose (Infraestrutura)

### Geração Dinâmica do docker-compose.yml

O arquivo `docker-compose.yml` é gerado dinamicamente a partir de um template. O processo utiliza `envsubst` para substituir variáveis e `sed` para habilitar/desabilitar serviços opcionais.

**Template:** `infra/orquestrators/docker-compose/docker-compose-template.yml`

**Fluxo de geração:**

```
envlocal.env  ──→  envsubst  ──→  sed (toggles)  ──→  docker-compose.yml
```

**Toggles de serviços (via `sed`):**

| Tag no Template | Variável de Controle | Serviço |
|-----------------|---------------------|---------|
| `#serviceldap` | `OPENLDAP_PRESENTE` | OpenLDAP + phpLDAPadmin |
| `#servicejod` | `JOD_PRESENTE` | JOD Converter |
| `#servicemail` | `MAIL_CATCHER_PRESENTE` | MailCatcher |
| `#servicememcachedadmin` | `MEMCACHEDADMIN_PRESENTE` | phpMemcachedAdmin |
| `#servicedbadmin` | `DBADMIN_PRESENTE` | Adminer |
| `#servicebal` | `BALANCEADOR_PRESENTE` | Traefik |
| `#app-traefik` | `BALANCEADOR_PRESENTE` | Labels Traefik no app |

Quando a variável é `true`, o `sed` remove o `#tag` do início das linhas correspondentes, ativando o serviço.

### Serviços Ativos (padrão)

| Serviço | Imagem | Função |
|---------|--------|--------|
| `storage-app` | `busybox:latest` | Sidecar para volumes |
| `storage-certs` | `busybox:latest` | Sidecar para certificados |
| `memcached` | `${DOCKER_IMAGE_MEMCACHED}` | Cache |
| `db` | `${DOCKER_IMAGE_BD}` | Banco de dados |
| `solr` | `${DOCKER_IMAGE_SOLR}` | Busca full-text |
| `app-atualizador` | `${DOCKER_IMAGE_APP}` | Instalação/atualização |
| `app-agendador` | `${DOCKER_IMAGE_APP_AGENDADOR}` | Jobs em background |
| `app` | `${DOCKER_IMAGE_APP}` | Aplicação web |

### Serviços Opcionais

| Serviço | Toggle | URL de Acesso |
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
# Ver todos os comandos disponíveis
make help

# Setup completo (cria volumes + sobe serviços)
make setup

# Apenas construir docker-compose.yml
make build_docker_compose

# Subir serviços (build + up -d)
make run

# Escalar aplicação (padrão 2 nós)
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
make logs                  # Todos os serviços
make logs_app              # Aplicação
make logs_app-atualizador  # Atualizador
make logs_balanceador      # Traefik
make logs_openldap         # OpenLDAP
make logs_solr             # Solr
```

### Health Checks

```bash
# Verifica se SEI responde com tela de login
make check-sei-isalive

# Verifica compatibilidade de versão fonte vs containers
make check-version-compatibility

# Verifica se fontes estão posicionados
make check-fontes-posicionado
```

---

## Kubernetes

### Geração de Manifests

Os manifests Kubernetes são gerados a partir de templates usando `envsubst`, similar ao docker-compose.

**Templates:** `infra/orquestrators/rancher-kubernetes/templates/`

| Template | Conteúdo |
|----------|----------|
| `deploys-svc-template.yaml` | Deployments e Services |
| `statefullsets-template.yaml` | StatefulSets |
| `configmaps-template.yaml` | ConfigMaps |
| `secrets-template.yaml` | Secrets (credenciais, certificados) |
| `pvc-template.yaml` | PersistentVolumeClaims |
| `jobs-template.yaml` | Jobs (one-time) |
| `ingress-template.yaml` | Ingress rules |

**Saída:** `infra/orquestrators/rancher-kubernetes/topublish/*.yaml`

### Comandos Kubernetes

```bash
# Gerar manifests a partir dos templates
make kubernetes_montar_yaml

# Aplicar no cluster
make kubernetes_apply

# Remover do cluster
make kubernetes_delete

# Verificar deploy específico
make kubernetes_check_deploy_generic KUBE_DEPLOY_NAME=app
```

### Configuração Kubernetes

As variáveis `KUBERNETES_*` controlam:
- **Namespace:** `KUBERNETES_NAMESPACE=seins`
- **Storage class:** `KUBERNETES_PVC_STORAGECLASS=nfs-client`
- **Recursos:** Limites e requests de CPU/memória por serviço
- **Timeout:** 180s para verificação de deploy

---

## Makefile de Containers (Build)

**Arquivo:** `containers/Makefile`

### Setup Inicial

```bash
cd containers

# Copiar template de configuração
make getenv

# Editar envcontainers.env
vi envcontainers.env
```

### Build de Imagens

```bash
# Build de TODAS as imagens (ordem correta de dependências)
make build-conteiners

# Build individual (exemplos)
make build-conteiner-base-rocky93
make build-conteiner-base-app-php8
make build-conteiner-app-ci-php8
make build-conteiner-app-ci-php8-agendador
make build-conteiner-mysql8-sei50
make build-conteiner-solr-9.6.1
make build-conteiner-traefik

# Build genérico (parametrizado)
make build-conteiner-generic \
  IMAGEMTMP=minha-imagem \
  IMAGEMTMP_VERSAO=1.0.0 \
  IMAGEMTMP_PATH=caminho/do/dockerfile
```

### Publicação no Registry

```bash
# Publicar todas as imagens
make publish-containers

# Publicar individual
make publish-container-app-ci-php8
make publish-container-mysql8-sei50
```

Cada publicação envia a tag versionada e `:latest` (exceto quando versão é "test").

### Limpeza

```bash
# Apagar TODAS as imagens locais
make erase-conteiners-local

# Apagar individual
make erase-conteiner-app-ci-php8
make erase-conteiner-mysql8-sei50
```

---

## Ordem de Build (Dependências)

A ordem correta de build respeita a hierarquia de imagens:

```
1. Imagens base do SO
   ├── base-centos7
   └── base-rocky93

2. Imagens base de serviço
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

4. Imagens da aplicação
   ├── app-ci (depende de base-app)
   ├── app-ci-php8 (depende de base-app-php8)
   ├── app-dev (depende de base-app)
   └── app-dev-php8 (depende de base-app-php8)

5. Imagens do agendador
   ├── app-ci-agendador (depende de app-ci)
   └── app-ci-php8-agendador (depende de app-ci-php8)

6. Demais serviços
   ├── solr8.2.0 (depende de base-centos7)
   ├── solr9.4.0, solr9.6.1 (depende de base-rocky93)
   ├── jod (depende de base-centos7)
   ├── jod4.4.8 (alpine, sem dependência)
   ├── traefik (depende de traefik-base)
   ├── openldap (depende de openldap-base)
   ├── memcached (sem dependência)
   └── mailcatcher (sem dependência)
```

---

## Modalidades de Deploy (Infra)

| Modalidade | Balanceador | HTTPS | Serviços Opcionais | Uso |
|------------|-------------|-------|---------------------|-----|
| **Reduzida** | Não | Sim | Nenhum | Teste mínimo |
| **Default** | Sim | Sim | JOD | Teste padrão |
| **Completa** | Sim | Sim | Todos (LDAP, Mail, DB Admin, etc) | Teste completo |
| **HTTP** | Variável | Não | Variável | Sem TLS |

Configuração via variáveis booleanas `*_PRESENTE` no `envlocal.env`.
