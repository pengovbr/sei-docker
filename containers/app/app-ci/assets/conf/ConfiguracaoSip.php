<?

class ConfiguracaoSip extends InfraConfiguracao  {

 	private static $instance = null;

 	public static function getInstance(){
 	  if (ConfiguracaoSip::$instance == null) {
 	    ConfiguracaoSip::$instance = new ConfiguracaoSip();
 	  }
 	  return ConfiguracaoSip::$instance;
 	}

 	public function getArrConfiguracoes(){
 	  return array(
 	      'Sip' => array(
 	          'URL' => getenv('APP_PROTOCOLO').'://'.getenv('APP_HOST').'/sip',
 	          'Producao' => true),

 	      'PaginaSip' => array('NomeSistema' => 'SIP'),

 	      'SessaoSip' => array(
 	          'SiglaOrgaoSistema' => getenv('APP_ORGAO'),
 	          'SiglaSistema' => 'SIP',
 	          'PaginaLogin' => getenv('APP_PROTOCOLO').'://'.getenv('APP_HOST').'/sip/login.php',
 	          'SipWsdl' => getenv('APP_PROTOCOLO').'://'.getenv('APP_HOST').'/sip/controlador_ws.php?servico=sip',
 	          'ChaveAcesso' => getenv('APP_SIP_CHAVE_ACESSO'), //ATENÇÃO: gerar uma nova chave para o SIP após a instalação (ver documento de instalação)
 	          'https' => (getenv('APP_PROTOCOLO') == 'https' ? true : false)),

 	      'BancoSip'  => array(
 	          'Servidor' => getenv('APP_DB_HOST'),
 	          'Porta' => getenv('APP_DB_PORTA'),
 	          'Banco' => getenv('APP_DB_SIP_BASE'),
 	          'Usuario' => getenv('APP_DB_SIP_USERNAME'),
 	          'Senha' => getenv('APP_DB_SIP_PASSWORD'),
 	          'UsuarioScript' => getenv('APP_DB_SIP_USERNAME'),
 	          'SenhaScript' => getenv('APP_DB_SIP_PASSWORD'),
 	          'Tipo' => getenv('APP_DB_TIPO')), //MySql, SqlServer, Oracle ou PostgreSql

        /*
 	      'BancoAuditoriaSip'  => array(
 	          'Servidor' => '[Servidor BD]',
 	          'Porta' => '',
 	          'Banco' => '',
 	          'Usuario' => '',
 	          'Senha' => '',
 	          'Tipo' => ''), //MySql, SqlServer, Oracle ou PostgreSql
        */

				'CacheSip' => array('Servidor' => 'memcached',
						                'Porta' => '11211'),

				'InfraMail' => array(
					'Tipo' => getenv('APP_MAIL_TIPO'), //1 = sendmail (neste caso não é necessário configurar os atributos abaixo), 2 = SMTP
					'Servidor' => getenv('APP_MAIL_SERVIDOR'),
					'Porta' => getenv('APP_MAIL_PORTA'),
					'Codificacao' => getenv('APP_MAIL_CODIFICACAO'), //8bit, 7bit, binary, base64, quoted-printable
					'MaxDestinatarios' => getenv('APP_MAIL_MAXDESTINATARIOS'), //numero maximo de destinatarios por mensagem
					'MaxTamAnexosMb' => getenv('APP_MAIL_MAXTAMANHOANEXOSMB'), //tamanho maximo dos anexos em Mb por mensagem
					'Seguranca' => getenv('APP_MAIL_SEGURANCA'), //TLS, SSL ou vazio
					'Autenticar' => getenv('APP_MAIL_AUTENTICAR'), //se true então informar Usuario e Senha
					'Usuario' => getenv('APP_MAIL_USUARIO'),
					'Senha' => getenv('APP_MAIL_SENHA'),
					'Protegido' => getenv('APP_MAIL_PROTEGIDO') //campo usado em desenvolvimento, se tiver um email preenchido entao todos os emails enviados terao o destinatario ignorado e substituído por este valor evitando envio incorreto de email
				)
 	  );
 	}
}
?>
