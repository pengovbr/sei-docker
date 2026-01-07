FROM alpine:3.21

# Cria grupo jodconverter e usuário jodconverter para rodar como não-root
RUN addgroup -g 1001 jodconverter && \
    adduser -D -u 1001 -G jodconverter -h /home/jodconverter -s /bin/sh jodconverter

ADD https://github.com/pengovbr/sei-docker-binarios/raw/refs/heads/main/pacoteslinux/jodconverter-4.4.8.jar /opt/app/
ADD assets/application.yaml /opt/app/

RUN apk add -f --no-cache \ 
        libreoffice \ 
        openjdk17-jdk \
        font-terminus \
        font-inconsolata \
        font-dejavu \
        font-noto \
        font-noto-cjk \
        font-awesome \
        font-noto-extra 
RUN chown -R jodconverter:jodconverter /opt/app

USER jodconverter

WORKDIR /opt/app
        
CMD java -Xmx2G -jar /opt/app/jodconverter-4.4.8.jar

EXPOSE 8080
