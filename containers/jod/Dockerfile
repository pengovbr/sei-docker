################################################################################
# Dockerfile de construção do container JOD utilizado pelo SEI
################################################################################

ARG IMAGEM_BASE=processoeletronico/base-centos7:latest

FROM ${IMAGEM_BASE}

############################# INÍCIO DA INSTALAÇÃO #############################
ENV TERM=xterm

ADD assets/install.sh \
    assets/entrypoint.sh \
    https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/jodconverter-tomcat-2.2.2.zip \
    /

RUN sh /install.sh && rm -rf /install.sh
############################## FIM DA INSTALACAO ###############################

USER jodconverter

ENTRYPOINT ["/entrypoint.sh"]
EXPOSE 8080