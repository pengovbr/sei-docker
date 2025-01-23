
-- TODO: Padronizar datafiles da base de dados em SQL Server
RESTORE DATABASE [sei] FROM  DISK = N'/tmp/sei_sqlserver.bak' WITH FILE = 1, NOUNLOAD, REPLACE, STATS = 5, 
MOVE 'gedoc' TO '/var/opt/mssql/data/sei_data.mdf', 
MOVE 'SEI_data3' TO '/var/opt/mssql/data/sei_data1.ndf',
MOVE 'SEI_data4' TO '/var/opt/mssql/data/sei_data2.ndf',
MOVE 'SEI_log2' TO '/var/opt/mssql/data/sei_data3.ndf',
MOVE 'Sei_Log3' TO '/var/opt/mssql/data/sei_data4.ndf',
MOVE 'gedoc_log' TO '/var/opt/mssql/data/sei_1og.ldf'
GO

-- TODO: Padronizar datafiles da base de dados em SQL Server
RESTORE DATABASE [sip] FROM  DISK = N'/tmp/sip_sqlserver.bak' WITH FILE = 1, NOUNLOAD, REPLACE, STATS = 5, 
MOVE 'sip_Data' TO '/var/opt/mssql/data/sip_data.mdf', 
MOVE 'sip_Log' TO '/var/opt/mssql/data/sip_log.ldf'
GO

USE sei;
GO

DROP USER IF EXISTS [sei_user]
GO

CREATE LOGIN sei_user
    WITH PASSWORD = 'sei_user', CHECK_POLICY=OFF;
GO

CREATE USER sei_user FOR LOGIN sei_user;
GO

EXEC sp_addrolemember 'db_owner', 'sei_user'
GO

update orgao set sigla='ABC', descricao='ORGAO ABC' where id_orgao=0;
GO

delete from auditoria_protocolo;
GO

USE sip;
GO

DROP USER IF EXISTS [sip_user]
GO

CREATE LOGIN sip_user
    WITH PASSWORD = 'sip_user', CHECK_POLICY=OFF;
GO

CREATE USER sip_user FOR LOGIN sip_user;
GO

EXEC sp_addrolemember 'db_owner', 'sip_user'
GO

update orgao set sigla='ABC', descricao='ORGAO ABC' where id_orgao=0;
GO

update sistema set pagina_inicial='http://localhost/sip' where sigla='SIP';
GO

update sistema set pagina_inicial='http://localhost/sei/inicializar.php', web_service='http://localhost/sei/controlador_ws.php?servico=sip' where sigla='SEI';
GO

update orgao set sin_autenticar='N' where id_orgao=0;
GO

delete from seq_infra_auditoria
GO
