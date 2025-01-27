################################################################################
# Dockerfile de construção do container Oracle utilizado pelo SEI
#
# Container preparado e configurado para uso em desenvolvimento e testes
# construa antes a imagem base
################################################################################

ARG IMAGEM_BASE=processoeletronico/base-oracle11g:latest

FROM ${IMAGEM_BASE}

#ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/feat/master
ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/master

ENV TERM xterm \
    ORACLE_ALLOW_REMOTE=true \
    NLS_LANG=PORTUGUESE_BRAZIL.WE8MSWIN1252

ADD ${GIT_DB_REF}/oracle/v5.0.0/sei_5_0_0_BD_Ref_Exec.dmp \
    ${GIT_DB_REF}/oracle/v5.0.0/sip_5_0_0_BD_Ref_Exec.dmp \
    /tmp/

COPY assets/pre-install.sql /tmp/pre-install.sql
COPY assets/sei-config.sql /tmp/sei-config.sql
COPY assets/sip-config.sql /tmp/sip-config.sql
COPY assets/install.sh /install.sh

RUN bash /install.sh
