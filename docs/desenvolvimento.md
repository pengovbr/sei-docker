# Ambiente de Desenvolvimento

Guia técnico para configuração e uso do ambiente de desenvolvimento local do SEI-Docker.

---

## Visão Geral

O ambiente de desenvolvimento (`dev/`) oferece uma configuração simplificada com:

- XDebug habilitado para debug remoto
- Código-fonte montado via volume (edição ao vivo)
- Portas mapeadas diretamente para o host
- Troca fácil entre bancos de dados e versões do SEI
- MailCatcher para captura de e-mails

---

## Estrutura

```
dev/
├── Makefile                    # Automação (up, down, config, update)
├── docker-compose.yml          # Orquestração simplificada
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

### 1. Pré-requisitos

- Docker Engine 20+
- Docker Compose v2+
- Código-fonte do SEI (obtido separadamente)
- GNU Make

### 2. Definir caminho dos fontes

```bash
export SEI_PATH=~/sei/FonteSEI
```

O diretório deve conter as pastas `sei/` e `sip/` com o código-fonte.

### 3. Escolher banco de dados e versão

```bash
cd dev

# Opções disponíveis:
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

| Serviço | URL | Credenciais |
|---------|-----|-------------|
| **SEI** | http://localhost:8000/sei | `teste` / `teste` |
| **SIP** | http://localhost:8000/sip | `teste` / `teste` |
| **MailCatcher** | http://localhost:1080 | -- |

---

## Comandos do Makefile

| Comando | Descrição |
|---------|-----------|
| `make help` | Mostra ajuda |
| `make up` | Sobe o ambiente (verifica pré-requisitos) |
| `make up-update` | Sobe e executa scripts de atualização do SEI/SIP |
| `make update` | Executa apenas os scripts de atualização |
| `make config base=X` | Troca o banco/versão (ver opções acima) |
| `make down` | Para o ambiente (preserva volumes) |
| `make restart` | Para e sobe novamente |
| `make destroy` | Para e apaga todos os volumes (DESTRUTIVO) |
| `make check-sei-path` | Verifica se o código-fonte existe |
| `make check-sei-isalive` | Verifica se o SEI responde com tela de login |
| `make check-sei-ispinging` | Verifica se o Apache está respondendo |

---

## Serviços do Docker Compose

| Serviço | Container | Imagem | Porta no Host |
|---------|-----------|--------|---------------|
| `httpd` | `httpd` | `app-dev-php8` | 8000 |
| `database` | `${DATABASE_HOST}` | Variável | Porta do banco |
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

## Configuração do XDebug

O container `httpd` (app-dev-php8) vem com XDebug 3 pré-configurado.

### Variáveis de Ambiente

| Variável | Valor Padrão | Descrição |
|----------|-------------|-----------|
| `XDEBUG_MODE` | `debug` | Modo: `debug`, `profile`, `trace`, `off` |
| `XDEBUG_SESSION` | `default` | Nome da sessão / IDE key |
| `XDEBUG_CONFIG` | `idekey=default client_host=${HOST_IP} client_port=9003 discover_client_host=1` | Config completa |

### Configurar IP do Host

Para que o XDebug se conecte à sua IDE, defina o IP do host:

```bash
# Linux
export HOST_IP=$(hostname -I | awk '{print $1}')

# WSL2
export HOST_IP=$(cat /etc/resolv.conf | grep nameserver | awk '{print $2}')

# macOS
export HOST_IP=$(ifconfig en0 | grep 'inet ' | awk '{print $2}')
```

### Configuração na IDE

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
3. Path mappings: `/opt/sei` -> diretório local `sei/`

---

## Montagem do Código-Fonte

O código-fonte é montado como volume em `/opt/` dentro do container `httpd`:

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

Alterações nos arquivos são refletidas imediatamente no container (sem rebuild).

---

## Troca de Banco de Dados

Para trocar o banco de dados ou a versão do SEI:

```bash
# Parar o ambiente atual
make down

# (Opcional) Apagar dados do banco anterior
make destroy

# Trocar configuração
make config base=postgres-sei5

# Subir com novo banco
make up
```

### Combinações Disponíveis

| Base | Banco | Versão SEI | Imagem |
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

## Atualização de Versão

Quando atualizar o código-fonte do SEI para uma nova versão, execute os scripts de atualização:

```bash
make update
```

Ou suba o ambiente já executando o update:

```bash
make up-update
```

Isso executa internamente:
1. `atualizar_versao_sip.php` - Atualiza schema do SIP
2. `atualizar_versao_sei.php` - Atualiza schema do SEI
3. `atualizar_recursos_sei.php` - Atualiza recursos do SEI

---

## Resolução de Problemas

### SEI não inicia

```bash
# Verificar se o caminho dos fontes está correto
make check-sei-path

# Verificar se o Apache está respondendo
make check-sei-ispinging

# Verificar se o SEI responde com login
make check-sei-isalive

# Ver logs do container
docker logs httpd
```

### Erro de conexão com banco

```bash
# Verificar se o container do banco está rodando
docker ps | grep -E "mysql|postgres|oracle|sqlserver"

# Ver logs do banco
docker logs $(docker ps -qf "name=mysql\|postgres\|oracle\|sqlserver")
```

### XDebug não conecta

1. Verificar se `HOST_IP` está definido corretamente
2. Verificar se a porta 9003 está aberta no firewall do host
3. Testar com: `XDEBUG_MODE=debug,develop` para mensagens de erro detalhadas
4. No WSL2, verificar regra de firewall do Windows

### Volumes com dados antigos

```bash
# Destruir tudo e recriar
make destroy
make up
```
