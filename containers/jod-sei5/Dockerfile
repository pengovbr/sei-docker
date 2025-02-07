FROM alpine:3.21

ADD assets/jodconverter-4.4.8.jar /opt/app/
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

CMD java -Xmx2G -jar /opt/app/jodconverter-4.4.8.jar
