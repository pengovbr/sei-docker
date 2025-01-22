################################################################################
# Dockerfile de construção do container SqlServer utilizado pelo SEI
#
# Container preparado e configurado para uso em desenvolvimento e testes
# SQL Server para Linux é uma versão experimental e não deve ser utilizada em
# ambiente de produção
# construa antes a imagem base
################################################################################

ARG IMAGEM_BASE=processoeletronico/base-sqlserver2017:latest

FROM ${IMAGEM_BASE}

ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/master

ENV TERM=xterm \
    ACCEPT_EULA=Y \
    SA_PASSWORD='yourStrong(!)Password'

ADD assets/install.sh \
    assets/restore.sql \
    ${GIT_DB_REF}/sqlserver/v4.0.0/sei_4_0_0_BD_Ref_Exec.bak \
    ${GIT_DB_REF}/sqlserver/v4.0.0/sip_4_0_0_BD_Ref_Exec.bak \
    /tmp/

RUN bash /tmp/install.sh
