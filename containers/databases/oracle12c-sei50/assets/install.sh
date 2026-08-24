#!/usr/bin/env bash
set -e

# Variáveis de ambiente
export ORACLE_ALLOW_REMOTE=true
export ORACLE_CHARACTERSET=WE8MSWIN1252
export ORACLE_SID=ORCLCDB
export ORACLE_PDB=ORCLPDB1

# Inicialização do servidor
home/oracle/setup/dockerInit.sh &

e=1
while [ ! $e -eq 0 ]; do
  sleep 5;
  echo "....Ignore-me. Processo alternativo verificando se banco subiu..."
  set +e
  ss -tunlp | grep 1521
  e=$?
  set -e
done
echo "Banco subiu. Vamos continuar..."
sleep 5

source /home/oracle/.bashrc

mkdir -p /u01/app/oracle/oradata/XE/

# Configuração do character set e outros parâmetros iniciais
sqlplus sys/oracle as sysdba @"/tmp/pre-install.sql"

e=1
while [ ! $e -eq 0 ]; do
  sleep 5;
  echo "....Ignore-me. Processo alternativo verificando se banco subiu..."
  set +e
  ss -tunlp | grep 1521
  e=$?
  set -e
done
echo "Banco subiu. Vamos continuar..."
sleep 5

ls -l /tmp
sleep 10

# Restauração das bases de dados do SEI e SIP
imp sip/sip_user@localhost:1521/orclpdb1.localdomain file=/tmp/sip_5_0_0_BD_Ref_Exec.dmp full=y
imp sei/sei_user@localhost:1521/orclpdb1.localdomain file=/tmp/sei_5_0_0_BD_Ref_Exec.dmp full=y

# Configuração das bases de dados do sistema
sqlplus sei/sei_user@localhost:1521/orclpdb1.localdomain @"/tmp/sei-config.sql"
sqlplus sip/sip_user@localhost:1521/orclpdb1.localdomain @"/tmp/sip-config.sql"

exit 0
