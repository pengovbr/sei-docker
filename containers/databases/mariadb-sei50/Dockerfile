################################################################################
# Dockerfile de construção do container Mariadb utilizado pelo SEI
# construa antes a imagem base
################################################################################

ARG IMAGEM_BASE=processoeletronico/base-mariadb10.5:latest

FROM ${IMAGEM_BASE} as builder

#ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/feat/master
ARG GIT_DB_REF=https://github.com/spbgovbr/sei-db-ref-executivo/raw/feat/atualizacoes_inclusao_5.0

############################# INÍCIO DA INSTALAÇÃO #############################
ENV MYSQL_ROOT_PASSWORD=P@ssword

COPY assets/my.cnf /etc/mysql/mariadb.conf.d/sei.cnf
COPY assets/pre-install.sql /docker-entrypoint-initdb.d/1_pre-install.sql
ADD ${GIT_DB_REF}/mysql/v5.0.0/sei_5_0_0_BD_Ref_Exec.sql /docker-entrypoint-initdb.d/2_sei_5_0_0_BD_Ref_Exec.sql
ADD ${GIT_DB_REF}/mysql/v5.0.0/sip_5_0_0_BD_Ref_Exec.sql /docker-entrypoint-initdb.d/3_sip_5_0_0_BD_Ref_Exec.sql
COPY assets/pos-install.sql /docker-entrypoint-initdb.d/9_pos-install.sql

RUN chmod 644 /etc/mysql/mariadb.conf.d/sei.cnf
RUN chmod +r /docker-entrypoint-initdb.d/2_sei_5_0_0_BD_Ref_Exec.sql
RUN chmod +r /docker-entrypoint-initdb.d/3_sip_5_0_0_BD_Ref_Exec.sql
RUN sed -i '1i use sei; \n' /docker-entrypoint-initdb.d/2_sei_5_0_0_BD_Ref_Exec.sql
RUN sed -i '1i use sip; \n' /docker-entrypoint-initdb.d/3_sip_5_0_0_BD_Ref_Exec.sql

RUN ["sed", "-i", "s/exec \"$@\"/echo \"not running $@\"/", "/usr/local/bin/docker-entrypoint.sh"]
RUN ["/usr/local/bin/docker-entrypoint.sh", "mysqld", "--datadir", "/initialized-db"]


############################## FIM DA INSTALAÇÃO ###########################
FROM ${IMAGEM_BASE}
COPY --from=builder /initialized-db /var/lib/mysql
COPY --from=builder /etc/mysql/mariadb.conf.d/sei.cnf /etc/mysql/mariadb.conf.d/sei.cnf
