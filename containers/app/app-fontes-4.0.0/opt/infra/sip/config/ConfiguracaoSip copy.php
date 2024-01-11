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
 	          'URL' => 'http://[Servidor PHP]/sip',
 	          'Producao' => true),
 	       
 	      'PaginaSip' => array('NomeSistema' => 'SIP'),

 	      'SessaoSip' => array(
 	          'SiglaOrgaoSistema' => 'ABC',
 	          'SiglaSistema' => 'SIP',
 	          'PaginaLogin' => 'http://[Servidor PHP]/sip/login.php',
 	          'SipWsdl' => 'http://[Servidor PHP]/sip/controlador_ws.php?servico=sip',
 	          'ChaveAcesso' => 'd27791b894028d9e7fa34887ad6f0c9a2c559cccda5f64f4e108e3573d5db862b66fb933', //ATEN��O: gerar uma nova chave para o SIP ap�s a instala��o (ver documento de instala��o)
 	          'https' => false),
 	       
 	      'BancoSip'  => array(
 	          'Servidor' => '[Servidor BD]',
 	          'Porta' => '',
 	          'Banco' => '',
 	          'Usuario' => '',
 	          'Senha' => '',
 	          'Tipo' => ''), //MySql, SqlServer, Oracle ou PostgreSql

        /*
 	      'BancoAuditoriaSip'  => array(
 	          'Servidor' => '[Servidor BD]',
 	          'Porta' => '',
 	          'Banco' => '',
 	          'Usuario' => '',
 	          'Senha' => '',
 	          'Tipo' => ''), //MySql, SqlServer, Oracle ou PostgreSql
        */

				'CacheSip' => array('Servidor' => '[Servidor Memcache]',
						                'Porta' => '11211'),

				'InfraMail' => array(
						'Tipo' => '1', //1 = sendmail (neste caso n�o � necess�rio configurar os atributos abaixo), 2 = SMTP
						'Servidor' => '[Servidor E-Mail]',
						'Porta' => '25',
						'Codificacao' => '8bit', //8bit, 7bit, binary, base64, quoted-printable
						'MaxDestinatarios' => 999, //numero maximo de destinatarios por mensagem
						'MaxTamAnexosMb' => 999, //tamanho maximo dos anexos em Mb por mensagem
						'Seguranca' => 'TLS', //TLS, SSL ou vazio
						'Autenticar' => false, //se true ent�o informar Usuario e Senha
						'Usuario' => 'aaa',
						'Senha' => 'aaa',
						'Protegido' => '' //campo usado em desenvolvimento, se tiver um email preenchido entao todos os emails enviados terao o destinatario ignorado e substitu�do por este valor (evita envio incorreto de email)
				)
 	  );
 	}
}
?>