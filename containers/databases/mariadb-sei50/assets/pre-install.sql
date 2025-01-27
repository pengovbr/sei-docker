
CREATE DATABASE sei;

USE sei;
CREATE USER 'sei_user'@'%' IDENTIFIED BY 'sei_user';
GRANT ALL PRIVILEGES ON sei.* TO 'sei_user'@'%';


CREATE DATABASE sip;

USE sip;
CREATE USER 'sip_user'@'%' IDENTIFIED BY 'sip_user';
GRANT ALL PRIVILEGES ON sip.* TO 'sip_user'@'%';

