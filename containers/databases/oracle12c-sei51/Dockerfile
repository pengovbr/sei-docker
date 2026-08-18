################################################################################
# Dockerfile de construção do container Oracle-12c utilizado pelo SEI
#
# Container preparado e configurado para uso em desenvolvimento e testes
# Baixe antes a imagem base no repositorio docker da Oracle:
# imagem docker: container-registry.oracle.com/database/enterprise:12.2.0.1-slim
# Nao podemos disponibilizar a mesma, pois a Oracle exige que cada usuario aceite
# os termos de uso para esta imagem da versao 12c
################################################################################

ARG IMAGEM_BASE=container-registry.oracle.com/database/enterprise:12.2.0.1-slim

FROM ${IMAGEM_BASE}

USER oracle

ARG GIT_DB_REF=https://github.com/pengovbr/sei-db-ref-executivo/raw/master

ENV TERM=xterm \
    ORACLE_ALLOW_REMOTE=true \
    ORACLE_CHARACTERSET=WE8MSWIN1252 \
    ORACLE_SID=ORCLCDB \
    ORACLE_PDB=ORCLPDB1 \
    ORACLE_PWD=admin


ADD --chown=oracle ${GIT_DB_REF}/oracle/v5.0.0/sei_5_0_0_BD_Ref_Exec.dmp \
    ${GIT_DB_REF}/oracle/v5.0.0/sip_5_0_0_BD_Ref_Exec.dmp \
    /tmp/

COPY assets/pre-install.sql /tmp/pre-install.sql
COPY assets/sei-config.sql /tmp/sei-config.sql
COPY assets/sip-config.sql /tmp/sip-config.sql
COPY assets/install.sh /install.sh

RUN bash /install.sh
