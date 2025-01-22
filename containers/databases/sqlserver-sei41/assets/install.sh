#!/usr/bin/env bash
set -e

# Variáveis de ambiente
export ACCEPT_EULA=Y
export SA_PASSWORD='yourStrong(!)Password'

# Instalação do FreeTDS para acesso ao SQL Server
#apt-get -y --allow-unauthenticated update
#apt-get -y --allow-unauthenticated install libodbc1 freetds-dev freetds-bin

/opt/mssql/bin/sqlservr &
sleep 30

mv /tmp/sip_4_1_0_BD_Ref_Exec.bak /tmp/sip_sqlserver.bak
mv /tmp/sei_4_1_0_BD_Ref_Exec.bak /tmp/sei_sqlserver.bak

/opt/mssql-tools18//bin/sqlcmd -S localhost -U sa -C -P $SA_PASSWORD < /tmp/restore.sql

# Remover arquivos temporários
rm -rf /tmp/*

exit 0
