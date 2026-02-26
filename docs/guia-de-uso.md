# Guia de Uso - SEI-Docker

Guia completo e unificado para instalação, configuração e uso do SEI-Docker em todas as suas modalidades: desenvolvimento, infraestrutura e build de containers.

---

## Índice

1. [Pré-requisitos](#1-pré-requisitos)
2. [Obter o Código-Fonte do SEI](#2-obter-o-código-fonte-do-sei)
3. [Início Rápido - Ambiente de Desenvolvimento](#3-início-rápido---ambiente-de-desenvolvimento)
4. [Início Rápido - Infraestrutura Completa](#4-início-rápido---infraestrutura-completa)
5. [Configuração do envlocal.env](#5-configuração-do-envlocalenv)
6. [Comandos Make - Referência Rápida](#6-comandos-make---referência-rápida)
7. [Acessando o SEI e Serviços](#7-acessando-o-sei-e-serviços)
8. [Trocar Banco de Dados](#8-trocar-banco-de-dados)
9. [Habilitar Serviços Opcionais](#9-habilitar-serviços-opcionais)
10. [Escalar a Aplicação](#10-escalar-a-aplicação)
11. [Alterar URL e Domínio](#11-alterar-url-e-domínio)
12. [Instalar Módulos](#12-instalar-módulos)
13. [Usar HTTPS com Certificado Próprio](#13-usar-https-com-certificado-próprio)
14. [Kubernetes](#14-kubernetes)
15. [Build de Imagens Customizadas](#15-build-de-imagens-customizadas)
16. [Credenciais Padrão](#16-credenciais-padrão)
17. [Resolução de Problemas](#17-resolução-de-problemas)
18. [Vídeo Tutoriais](#18-vídeo-tutoriais)
19. [Dicas e Boas Práticas](#19-dicas-e-boas-práticas)

---

## 1. Pré-requisitos

### Sistema Operacional
- Linux (recomendado) ou macOS
- Windows com WSL2

### Software

| Software | Versão Mínima | Verificar |
|----------|--------------|-----------|
| Docker Engine | 20+ | `docker --version` |
| Docker Compose | v2+ | `docker compose version` |
| GNU Make | qualquer | `make --version` |
| envsubst | qualquer | `envsubst --version` |
| curl | qualquer | `curl --version` |
| Git | qualquer | `git --version` |

### Obrigatório
- **Código-fonte do SEI** (propriedade do TRF4, obtido separadamente via [processoeletronico.gov.br](http://processoeletronico.gov.br))

> **Atenção:** O código-fonte do SEI é propriedade do TRF4. Sob nenhuma hipótese deve ser distribuído, emprestado ou salvo em qualquer lugar que não seja privativo da TI do órgão.

---

## 2. Obter o Código-Fonte do SEI

O código-fonte deve estar organizado na seguinte estrutura:

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

Defina o caminho como variável de ambiente:
```bash
export SEI_PATH=~/sei/FonteSEI
```

---

## 3. Início Rápido - Ambiente de Desenvolvimento

O ambiente **dev** é a forma mais simples de subir o SEI localmente, com XDebug habilitado e código-fonte montado para edição ao vivo.

### Passo a passo

```bash
# 1. Clonar o projeto
git clone https://github.com/pengovbr/sei-docker.git
cd sei-docker/dev

# 2. Definir caminho do código-fonte
export SEI_PATH=~/sei/FonteSEI

# 3. Subir o ambiente (MySQL como padrão)
make up
```

### Acessar

| Serviço | URL | Credenciais |
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

Opções: `mysql-sei4`, `mysql-sei5`, `oracle-sei4`, `oracle-sei5`, `postgres-sei4`, `postgres-sei5`, `sqlserver-sei4`, `sqlserver-sei5`

---

## 4. Início Rápido - Infraestrutura Completa

O ambiente **infra** é a forma completa de provisionar o SEI com todos os serviços, load balancer, HTTPS e possibilidade de escalonamento.

### Passo a passo

```bash
# 1. Clonar o projeto
git clone https://github.com/pengovbr/sei-docker.git
cd sei-docker/infra

# 2. Verificar e ajustar a variável LOCALIZACAO_FONTES_SEI no envlocal.env
#    Apontar para o diretório onde está o código-fonte do SEI
vi envlocal.env

# 3. Subir tudo com um único comando
make setup
```

O `make setup` executa automaticamente:
1. Valida compatibilidade de versão
2. Cria todos os volumes Docker necessários
3. Gera o `docker-compose.yml` a partir do template
4. Sobe todos os serviços

### Acessar

Com a configuração padrão (`APP_HOST=localhost`, `APP_PROTOCOLO=https`):

| Serviço | URL |
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

> **Importante:** Antes de rodar `make run` ou `make setup` após alterar o `envlocal.env`, sempre rode `make clear` primeiro. Isso evita serviços órfãos rodando quando se remove serviços da configuração.

---

## 5. Configuração do envlocal.env

O arquivo `infra/envlocal.env` é o coração da configuração. Ele contém 100+ variáveis que controlam todo o ambiente.

### Abordagem recomendada

> **Não altere tudo de uma vez.** Suba primeiro com as configurações padrão. Teste. Depois desligue, altere um parâmetro, suba novamente. Repita até compreender todo o ecossistema.

### Variáveis mais importantes para começar

| Variável | O que faz | Padrão |
|----------|-----------|--------|
| `LOCALIZACAO_FONTES_SEI` | Caminho do código-fonte no host | `~/sei/FonteSEI` |
| `LOCALIZACAO_CERTS` | Caminho dos certificados SSL | `~/sei/certs` |
| `APP_PROTOCOLO` | `http` ou `https` | `https` |
| `APP_HOST` | Hostname/URL do SEI | `localhost` |
| `APP_ORGAO` | Sigla do órgão | `ME` |
| `APP_ORGAO_DESCRICAO` | Nome completo do órgão | `Órgão Processo Eletrônico - MySql` |
| `APP_DB_TIPO` | Tipo do banco | `MySql` |
| `DOCKER_IMAGE_BD` | Imagem do banco de dados | `processoeletronico/mariadb10.5-sei40:latest` |

Para a referência completa de todas as variáveis, consulte [variaveis-ambiente.md](variaveis-ambiente.md).

---

## 6. Comandos Make - Referência Rápida

### Ambiente Dev (`cd dev`)

| Comando | Descrição |
|---------|-----------|
| `make help` | Lista todos os comandos |
| `make up` | Sobe o ambiente |
| `make up-update` | Sobe e executa scripts de atualização |
| `make update` | Executa scripts de atualização do SEI/SIP |
| `make config base=X` | Troca banco/versão |
| `make down` | Para o ambiente (preserva volumes) |
| `make restart` | Para e sobe novamente |
| `make destroy` | Para e apaga tudo (DESTRUTIVO) |
| `make check-sei-path` | Verifica código-fonte |
| `make check-sei-isalive` | Verifica se SEI responde |

### Infraestrutura (`cd infra`)

| Comando | Descrição |
|---------|-----------|
| `make help` | Lista todos os comandos |
| `make setup` | Setup completo (volumes + run) |
| `make run` | Gera docker-compose e sobe |
| `make build_docker_compose` | Apenas gera o docker-compose.yml |
| `make criar_volumes` | Cria todos os volumes |
| `make scale` | Escala app para 2 nós |
| `make scale qtd=N` | Escala app para N nós |
| `make stop` | Para containers |
| `make clear` | Para e remove containers (preserva volumes) |
| `make apagar_volumes` | Apaga TODOS os volumes (DESTRUTIVO) |
| `make logs` | Logs de todos os serviços |
| `make logs_app` | Logs da aplicação |
| `make logs_balanceador` | Logs do Traefik |
| `make check-sei-isalive` | Verifica se SEI responde |

### Build de Containers (`cd containers`)

| Comando | Descrição |
|---------|-----------|
| `make help` | Lista todos os comandos |
| `make getenv` | Cria envcontainers.env a partir do modelo |
| `make build-conteiners` | Build de todas as imagens |
| `make erase-conteiners-local` | Apaga todas as imagens locais |
| `make publish-containers` | Publica todas no registry |

---

## 7. Acessando o SEI e Serviços

### Infraestrutura (com load balancer)

Todos os serviços são acessados pela mesma URL base (definida em `APP_HOST`):

| Serviço | Caminho | Toggle |
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

| Serviço | URL |
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

### Imagens de banco disponíveis

| Imagem | Banco | Versão SEI |
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

## 9. Habilitar Serviços Opcionais

Edite `infra/envlocal.env` e altere os toggles:

```bash
# Converter documentos (LibreOffice)
JOD_PRESENTE=true

# Captura de e-mails (teste)
MAIL_CATCHER_PRESENTE=true

# Administração do banco via web
DBADMIN_PRESENTE=true

# Administração do cache
MEMCACHEDADMIN_PRESENTE=true

# Diretório LDAP (autenticação)
OPENLDAP_PRESENTE=true

# Load balancer Traefik
BALANCEADOR_PRESENTE=true
```

Depois aplique:
```bash
make clear
make run
```

> Quando `OPENLDAP_PRESENTE=true`, o SEI passa a usar autenticação LDAP automaticamente. Um usuário de teste com senha `123456` é criado. Para voltar ao login padrão, configure `OPENLDAP_PRESENTE=false` e `OPENLDAP_DESLIGAR_NO_ORGAO_0=true`, rode `make run`, depois volte `OPENLDAP_DESLIGAR_NO_ORGAO_0=false`.

---

## 10. Escalar a Aplicação

O SEI pode ser escalado horizontalmente (múltiplos nós de aplicação atrás do Traefik):

```bash
cd infra

# Escalar para 2 nós (padrão)
make scale

# Escalar para 3 nós
make scale qtd=3

# Voltar para 1 nó
make scale qtd=1
```

> Para balanceamento sem sticky session, habilite sessões no Memcached: `APP_MEMCACHED_SESSION=true` (requer SEI 5+).

---

## 11. Alterar URL e Domínio

### Trocar de localhost para um domínio personalizado

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

## 12. Instalar Módulos

Cada módulo é controlado por variáveis `MODULO_*_INSTALAR` e `MODULO_*_VERSAO` no `envlocal.env`.

### Exemplo: Habilitar módulo de Estatísticas

```bash
MODULO_ESTATISTICAS_INSTALAR=true
MODULO_ESTATISTICAS_VERSAO=master
```

### Exemplo: Habilitar módulo PEN (tramitação entre órgãos)

```bash
MODULO_PEN_INSTALAR=true
MODULO_PEN_VERSAO=master
MODULO_PEN_WEBSERVICE=https://homolog.api.processoeletronico.gov.br/interoperabilidade/soap/v3/
MODULO_PEN_CERTIFICADO_SENHA=1234
MODULO_PEN_CERTIFICADO_BASE64=<certificado em base64>
```

### Módulos disponíveis

| Módulo | Variável | Função |
|--------|----------|--------|
| Estatísticas | `MODULO_ESTATISTICAS_INSTALAR` | Painel de estatísticas |
| REST / WSSEI | `MODULO_REST_INSTALAR` | API REST |
| Gestão Documental | `MODULO_GESTAODOCUMENTAL_INSTALAR` | Gestão do ciclo de vida |
| Resposta | `MODULO_RESPOSTA_INSTALAR` | Respostas a demandas |
| Login Único | `MODULO_LOGINUNICO_INSTALAR` | SSO GOV.BR |
| Assinatura | `MODULO_ASSINATURA_INSTALAR` | Assinatura digital ICP-Brasil |
| PEN | `MODULO_PEN_INSTALAR` | Tramitação entre órgãos |
| Peticionamento | `MODULO_PETICIONAMENTO_INSTALAR` | Peticionamento eletrônico |
| Protocolo Integrado | `MODULO_PI_INSTALAR` | Integração Protocolo.GOV.BR |
| INCOM | `MODULO_INCOM_INSTALAR` | Publicação na Imprensa Nacional |

Para detalhes de cada módulo e suas variáveis, consulte [variaveis-ambiente.md](variaveis-ambiente.md).

Após alterar módulos:
```bash
make clear
make run
```

O container `app-atualizador` detecta e instala automaticamente os módulos habilitados.

---

## 13. Usar HTTPS com Certificado Próprio

### Certificado auto-assinado (padrão)

Por padrão, o projeto gera certificados auto-assinados automaticamente. Basta:
```bash
APP_PROTOCOLO=https
```

### Certificado próprio

1. Crie o diretório de certificados definido em `LOCALIZACAO_CERTS`:
```bash
mkdir -p ~/sei/certs
```

2. Copie seus arquivos de certificado para esse diretório

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

# Ajuste as variáveis KUBERNETES_* no envlocal.env
vi envlocal.env

# Gerar YAML
make kubernetes_montar_yaml
```

Os arquivos são gerados em `infra/orquestrators/rancher-kubernetes/topublish/`.

### Aplicar no cluster

```bash
make kubernetes_apply
```

### Remover do cluster

```bash
make kubernetes_delete
```

### Configuração Kubernetes no envlocal.env

```bash
KUBERNETES_NAMESPACE=seins
KUBERNETES_PVC_STORAGECLASS=nfs-client
KUBERNETES_RESOURCES_INFORMAR=true
KUBERNETES_LIMITS_MEMORY_APP=1Gi
KUBERNETES_LIMITS_CPU_APP=500m
# ... (ver variaveis-ambiente.md para lista completa)
```

> **Nota:** A geração Kubernetes atualmente funciona apenas para MySQL. O código-fonte do SEI deve ser movido manualmente para o PVC `vol-sei-fontes`.

---

## 15. Build de Imagens Customizadas

Se você deseja buildar suas próprias imagens ou usar seu próprio registry:

```bash
cd containers

# Criar arquivo de configuração
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

> Se alterar `DOCKER_REGISTRY` ou `DOCKER_CONTAINER_VERSAO_PRODUTO` nos containers, replique as alterações nos envfiles de `dev/` e `infra/`.

---

## 16. Credenciais Padrão

### Aplicação SEI/SIP

| Campo | Valor |
|-------|-------|
| Usuário | `teste` |
| Senha | `teste` |

### Bancos de Dados

#### MySQL / MariaDB
| Usuário | Login | Senha |
|---------|-------|-------|
| Root | `root` | `P@ssword` |
| SEI | `sei_user` | `sei_user` |
| SIP | `sip_user` | `sip_user` |

Acesso: `mysql -h 127.0.0.1 -u root -p sei`

#### PostgreSQL
| Usuário | Login | Senha |
|---------|-------|-------|
| Root | `postgres` | `P@ssword` |
| SEI | `sei_user` | `sei_user` |
| SIP | `sip_user` | `sip_user` |

#### Oracle
| Usuário | Login | Senha |
|---------|-------|-------|
| SYS | `sys` | `P@ssword` |
| System | `system` | `P@ssword` |
| SEI | `sei` | `sei_user` |
| SIP | `sip` | `sip_user` |

Acesso: `sqlplus sys/P@ssword as sysdba`

#### SQL Server
| Usuário | Login | Senha |
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
| Usuário teste | senha `123456` |

---

## 17. Resolução de Problemas

### Portas ocupadas

Antes de subir, verifique se as portas estão livres:

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

Se alguma porta estiver ocupada, pare o serviço ou altere o mapeamento no `envlocal.env`.

### SEI não inicia / tela em branco

```bash
# Verificar se SEI responde
make check-sei-isalive

# Ver logs da aplicação
make logs_app

# Ver logs do atualizador (instalação de módulos)
make logs_app-atualizador

# Ver todos os logs
make logs
```

### Serviços órfãos após mudança de configuração

```bash
# Sempre rode clear antes de alterar envlocal.env
make clear
# Agora edite envlocal.env
make run
```

### Container do banco não inicia

```bash
# Ver logs do banco
docker logs $(docker ps -aqf "name=db" | head -1)

# Recriar volume do banco (perde dados)
make apagar_volume_banco
make criar_volume_banco
make run
```

### Recomeçar do zero

```bash
cd infra
make clear
make apagar_volumes
# Edite envlocal.env se necessário
make setup
```

### Versão incompatível

```bash
# Verificar compatibilidade
make check-version-compatibility
```

O projeto valida se a versão do código-fonte é compatível com as imagens Docker.

### Docker Compose v1 vs v2

O projeto detecta automaticamente. Se tiver problemas:
```bash
# Verificar qual está instalado
docker compose version    # v2
docker-compose --version  # v1
```

---

## 18. Vídeo Tutoriais

Vídeos gravados pela equipe do projeto com orientações práticas:

| Vídeo | Conteúdo | Link |
|-------|----------|------|
| Infra Reduzida Rapidamente | Subida básica em localhost com HTTPS | [Assistir](https://www.youtube.com/watch?v=FwPp9lZiHuM) |
| Infra Completa Rapidamente | Todos os componentes, domain name, HTTPS | [Assistir](https://www.youtube.com/watch?v=MpTLtDlSVLw) |
| SqlServer ou Oracle | Como usar bancos alternativos | [Assistir](https://www.youtube.com/watch?v=IgEiR5CZEEs) |
| Organização do Projeto | Estrutura técnica e anatomia da solução | [Assistir](https://www.youtube.com/watch?v=rczbANlWVRY) |
| Customizações Básicas | Trocar hostname, Solr admin, scale | [Assistir](https://www.youtube.com/watch?v=HjZfryu0sco) |
| Customizações Completas | Todos os componentes, LDAP, orientações avançadas | [Assistir](https://www.youtube.com/watch?v=m5wXBPDMVQQ) |

> Recomendado assistir na ordem listada acima.

---

## 19. Dicas e Boas Práticas

1. **Comece simples.** Suba com as configurações padrão primeiro. Teste. Depois vá customizando um parâmetro por vez.

2. **Sempre `make clear` antes de alterar.** Evita serviços órfãos e conflitos.

3. **Não altere o envlocal.env inteiro de uma vez.** Altere um parâmetro, suba, teste, repita.

4. **Volumes são seus dados.** `make apagar_volumes` apaga banco de dados, arquivos anexados, índices Solr. Use com cuidado.

5. **`make clear` é seguro.** Remove apenas containers e redes, nunca dados.

6. **Verifique portas antes de subir.** Portas 80 e 443 (infra) ou 8000 (dev) devem estar livres.

7. **Use `make help`.** Todos os Makefiles possuem ajuda embutida com descrição de cada comando.

8. **DNS ou /etc/hosts.** Ao usar um domínio customizado, garanta que o DNS ou `/etc/hosts` aponte para a máquina correta.

9. **Backup dos volumes.** Em ambiente de teste/treinamento, faça backup do volume do banco (`VOLUME_DB`) e dos arquivos externos (`VOLUME_ARQUIVOSEXTERNOS`).

10. **Não é para produção.** O projeto é para DTH. Produção requer hardening de segurança, firewall, backup e configurações adicionais conforme documentação do TRF e PEN.
