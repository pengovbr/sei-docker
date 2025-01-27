################################################################################
# Dockerfile de construção do container Postgres utilizado pelo SEI
#
# Container preparado e configurado para uso em desenvolvimento e testes
# construa antes a imagem base
################################################################################

ARG IMAGEM_BASE=processoeletronico/base-postgres15:latest

FROM ${IMAGEM_BASE} as builder

ARG VERSAO_BASE=5.0.0
ARG NOME_FILE=5_0_0
#ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/feat/master
ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/master


############################# INÍCIO DA INSTALACAO #############################
ENV POSTGRES_PASSWORD=P@ssword \
    POSTGRES_HOST_AUTH_METHOD=scram-sha-256 \
    POSTGRES_INITDB_ARGS=--auth-host=scram-sha-256 \
    PGDATA=/initialized-db

ADD ${GIT_DB_REF}/postgresql/v${VERSAO_BASE}/sei_${NOME_FILE}_BD_Ref_Exec.sql \
    ${GIT_DB_REF}/postgresql/v${VERSAO_BASE}/sip_${NOME_FILE}_BD_Ref_Exec.sql \
    assets/* \
    /tmp/

RUN mv /tmp/postgres.conf /etc/postgresql/postgresql.conf && \
    mv /tmp/pre-install.sql /docker-entrypoint-initdb.d/1_pre-install.sql && \
    mv /tmp/sei_${NOME_FILE}_BD_Ref_Exec.sql /docker-entrypoint-initdb.d/2_sei.sql && \
    mv /tmp/sip_${NOME_FILE}_BD_Ref_Exec.sql /docker-entrypoint-initdb.d/3_sip.sql && \
    mv /tmp/pos-install.sql /docker-entrypoint-initdb.d/9_pos-install.sql && \
    mkdir /initialized-db && chown -R postgres /initialized-db && \
    chmod -R 777 /docker-entrypoint-initdb.d/ && \
    chmod 644 /etc/postgresql/postgresql.conf && \
    sed -i '1i \\\c sei; \n' /docker-entrypoint-initdb.d/2_sei.sql && \
    sed -i '1i \\\c sip; \n' /docker-entrypoint-initdb.d/3_sip.sql

# RUN su postgres -c "initdb -D /initialized-db"
RUN ["sed", "-i", "s/exec \"$@\"/echo \"not running $@\"/", "/usr/local/bin/docker-entrypoint.sh"]
RUN ["/usr/local/bin/docker-entrypoint.sh", "postgres"]

# ############################## FIM DA INSTALAÇÃO ###########################
FROM ${IMAGEM_BASE}

COPY --from=builder /initialized-db /var/lib/postgresql/data
COPY --from=builder /etc/postgresql/postgresql.conf /var/lib/postgresql/data/postgresql.conf
