# Ambiente de Desenvolvimento

Guia tecnico para configuracao e uso do ambiente de desenvolvimento local do SEI-Docker.

---

## Visao Geral

O ambiente de desenvolvimento (`dev/`) oferece uma configuracao simplificada com:

- XDebug habilitado para debug remoto
- Codigo-fonte montado via volume (edicao ao vivo)
- Portas mapeadas diretamente para o host
- Troca facil entre bancos de dados e versoes do SEI
- MailCatcher para captura de e-mails

---

## Estrutura

```
dev/
├── Makefile                    # Automacao (up, down, config, update)
├── docker-compose.yml          # Orquestracao simplificada
├── envs/                       # Templates de ambiente
│   ├── env-mysql-sei4.env
│   ├── env-mysql-sei5.env
│   ├── env-oracle-sei4.env
│   ├── env-oracle-sei5.env
│   ├── env-postgres-sei4.env
│   ├── env-postgres-sei5.env
│   ├── env-sqlserver-sei4.env
│   └── env-sqlserver-sei5.env
└── tests/                      # Testes funcionais
    ├── Makefile
    └── Selenium/
```

---

## Quick Start

### 1. Pre-requisitos

- Docker Engine 20+
- Docker Compose v2+
- Codigo-fonte do SEI (obtido separadamente)
- GNU Make

### 2. Definir caminho dos fontes

```bash
export SEI_PATH=~/sei/FonteSEI
```

O diretorio deve conter as pastas `sei/` e `sip/` com o codigo-fonte.

### 3. Escolher banco de dados e versao

```bash
cd dev

# Opcoes disponiveis:
# mysql-sei4, mysql-sei5, oracle-sei4, oracle-sei5
# postgres-sei4, postgres-sei5, sqlserver-sei4, sqlserver-sei5

make config base=mysql-sei5
```

Isso copia o template `envs/env-mysql-sei5.env` para `env.env`.

### 4. Subir o ambiente

```bash
make up
```

### 5. Acessar o SEI

| Servico | URL | Credenciais |
|---------|-----|-------------|
| **SEI** | http://localhost:8000/sei | `teste` / `teste` |
| **SIP** | http://localhost:8000/sip | `teste` / `teste` |
| **MailCatcher** | http://localhost:1080 | -- |

---

## Comandos do Makefile

| Comando | Descricao |
|---------|-----------|
| `make help` | Mostra ajuda |
| `make up` | Sobe o ambiente (verifica pre-requisitos) |
| `make up-update` | Sobe e executa scripts de atualizacao do SEI/SIP |
| `make update` | Executa apenas os scripts de atualizacao |
| `make config base=X` | Troca o banco/versao (ver opcoes acima) |
| `make down` | Para o ambiente (preserva volumes) |
| `make restart` | Para e sobe novamente |
| `make destroy` | Para e apaga todos os volumes (DESTRUTIVO) |
| `make check-sei-path` | Verifica se o codigo-fonte existe |
| `make check-sei-isalive` | Verifica se o SEI responde com tela de login |
| `make check-sei-ispinging` | Verifica se o Apache esta respondendo |

---

## Servicos do Docker Compose

| Servico | Container | Imagem | Porta no Host |
|---------|-----------|--------|---------------|
| `httpd` | `httpd` | `app-dev-php8` | 8000 |
| `database` | `${DATABASE_HOST}` | Variavel | Porta do banco |
| `memcached` | `memcached` | `memcached` | 11211 |
| `solr` | `solr` | `solr9.6.1` | 8983 |
| `jod` | `jod` | `jod4.4.8` | -- |
| `smtp` | `smtp` | `mailcatcher` | 1080 |

### Portas por Banco de Dados

| Banco | Porta |
|-------|-------|
| MySQL | 3306 |
| PostgreSQL | 5432 |
| Oracle | 1521 |
| SQL Server | 1433 |

---

## Configuracao do XDebug

O container `httpd` (app-dev-php8) vem com XDebug 3 pre-configurado.

### Variaveis de Ambiente

| Variavel | Valor Padrao | Descricao |
|----------|-------------|-----------|
| `XDEBUG_MODE` | `debug` | Modo: `debug`, `profile`, `trace`, `off` |
| `XDEBUG_SESSION` | `default` | Nome da sessao / IDE key |
| `XDEBUG_CONFIG` | `idekey=default client_host=${HOST_IP} client_port=9003 discover_client_host=1` | Config completa |

### Configurar IP do Host

Para que o XDebug se conecte a sua IDE, defina o IP do host:

```bash
# Linux
export HOST_IP=$(hostname -I | awk '{print $1}')

# WSL2
export HOST_IP=$(cat /etc/resolv.conf | grep nameserver | awk '{print $2}')

# macOS
export HOST_IP=$(ifconfig en0 | grep 'inet ' | awk '{print $2}')
```

### Configuracao na IDE

**VS Code (launch.json):**
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for XDebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/opt/sei": "${workspaceFolder}/sei",
        "/opt/sip": "${workspaceFolder}/sip",
        "/opt/infra": "${workspaceFolder}/infra"
      }
    }
  ]
}
```

**PHPStorm:**
1. Settings > PHP > Debug > Xdebug: Porta 9003
2. Settings > PHP > Servers: Host `localhost`, Porta `8000`
3. Path mappings: `/opt/sei` -> diretorio local `sei/`

---

## Montagem do Codigo-Fonte

O codigo-fonte e montado como volume em `/opt/` dentro do container `httpd`:

```yaml
volumes:
  - ${SEI_PATH}:/opt/
```

**Estrutura esperada do `SEI_PATH`:**
```
${SEI_PATH}/
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

Alteracoes nos arquivos sao refletidas imediatamente no container (sem rebuild).

---

## Troca de Banco de Dados

Para trocar o banco de dados ou a versao do SEI:

```bash
# Parar o ambiente atual
make down

# (Opcional) Apagar dados do banco anterior
make destroy

# Trocar configuracao
make config base=postgres-sei5

# Subir com novo banco
make up
```

### Combinacoes Disponiveis

| Base | Banco | Versao SEI | Imagem |
|------|-------|------------|--------|
| `mysql-sei4` | MariaDB 10.5 | 4.0 | `mariadb10.5-sei40` |
| `mysql-sei5` | MySQL 8 | 5.0 | `mysql8-sei50` |
| `oracle-sei4` | Oracle 11g | 4.0 | `oracle11g-sei40` |
| `oracle-sei5` | Oracle 11g | 5.0 | `oracle11g-sei50` |
| `postgres-sei4` | PostgreSQL 15 | 4.0 | `postgres15-sei40` |
| `postgres-sei5` | PostgreSQL 15 | 5.0 | `postgres15-sei50` |
| `sqlserver-sei4` | SQL Server 2019 | 4.0 | `sqlserver2019-sei40` |
| `sqlserver-sei5` | SQL Server 2019 | 5.0 | `sqlserver2019-sei50` |

---

## Atualizacao de Versao

Quando atualizar o codigo-fonte do SEI para uma nova versao, execute os scripts de atualizacao:

```bash
make update
```

Ou suba o ambiente ja executando o update:

```bash
make up-update
```

Isso executa internamente:
1. `atualizar_versao_sip.php` - Atualiza schema do SIP
2. `atualizar_versao_sei.php` - Atualiza schema do SEI
3. `atualizar_recursos_sei.php` - Atualiza recursos do SEI

---

## Resolucao de Problemas

### SEI nao inicia

```bash
# Verificar se o caminho dos fontes esta correto
make check-sei-path

# Verificar se o Apache esta respondendo
make check-sei-ispinging

# Verificar se o SEI responde com login
make check-sei-isalive

# Ver logs do container
docker logs httpd
```

### Erro de conexao com banco

```bash
# Verificar se o container do banco esta rodando
docker ps | grep -E "mysql|postgres|oracle|sqlserver"

# Ver logs do banco
docker logs $(docker ps -qf "name=mysql\|postgres\|oracle\|sqlserver")
```

### XDebug nao conecta

1. Verificar se `HOST_IP` esta definido corretamente
2. Verificar se a porta 9003 esta aberta no firewall do host
3. Testar com: `XDEBUG_MODE=debug,develop` para mensagens de erro detalhadas
4. No WSL2, verificar regra de firewall do Windows

### Volumes com dados antigos

```bash
# Destruir tudo e recriar
make destroy
make up
```
