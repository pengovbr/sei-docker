#!/usr/bin/env bash
set -e

# Variáveis de ambiente
export ORACLE_HOME=/u01/app/oracle/product/11.2.0/xe
export PATH=$ORACLE_HOME/bin:$PATH
export ORACLE_SID=XE
export NLS_LANG=PORTUGUESE_BRAZIL.WE8MSWIN1252

#mv /tmp/sei_4_0_0_BD_Ref_Exec.dmp /tmp/sei_4_0_0_BD_Ref_Exec.dmp
#mv /tmp/sip_4_0_0_BD_Ref_Exec.dmp /tmp/sip_4_0_0_BD_Ref_Exec.dmp

# Inicialização do servidor
/usr/sbin/startup.sh

# Configuração do character set e outros parâmetros iniciais
sqlplus sys/oracle as sysdba @"/tmp/pre-install.sql"

# Restauração das bases de dados do SEI e SIP
imp sei/sei_user file=/tmp/sei_5_0_0_BD_Ref_Exec.dmp full=y
imp sip/sip_user file=/tmp/sip_5_0_0_BD_Ref_Exec.dmp full=y

# Configuração das bases de dados do sistema
sqlplus sei/sei_user @"/tmp/sei-config.sql"
sqlplus sip/sip_user @"/tmp/sip-config.sql"

# Remover arquivos temporários
rm -rf /tmp/*

exit 0
