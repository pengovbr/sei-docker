################################################################################
# Dockerfile de construcao do container Solr utilizado pelo SEI
#
# Container preparado e configurado para uso em desenvolvimento e testes
################################################################################

ARG IMAGEM_BASE=processoeletronico/base-rocky93:latest

FROM ${IMAGEM_BASE}

ENV TERM xterm

ADD assets/ \
   https://archive.apache.org/dist/solr/solr/9.6.1/solr-9.6.1.tgz \
   https://github.com/spbgovbr/sei-docker-binarios/raw/main/pacoteslinux/msttcore-fonts-2.0-3.noarch.rpm \
   /tmp/

RUN ls -lh /tmp && sh /tmp/install.sh && rm -rf /tmp/*

USER solr

EXPOSE 8983
CMD ["/command.sh"]
