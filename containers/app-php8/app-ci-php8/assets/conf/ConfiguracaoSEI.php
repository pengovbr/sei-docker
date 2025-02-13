<?

class ConfiguracaoSEI extends InfraConfiguracao
{

  private static $instance = null;

  public static function getInstance()
  {
    if (ConfiguracaoSEI::$instance==null) {
      ConfiguracaoSEI::$instance = new ConfiguracaoSEI();
    }
    return ConfiguracaoSEI::$instance;
  }

  public function getArrConfiguracoes()
  {
    return array(

        'SEI' => array(
            'URL' => getenv('APP_PROTOCOLO').'://'.getenv('APP_HOST').'/sei',
            'Producao' => true,
            'RepositorioArquivos' => '/sei/arquivos_externos_sei',
            'WebServices' => true,
            'Modulos' => array(/*novomodulo*/),
        ),

        /*extramodulesconfig*/

        'PaginaSEI' => array(
            'NomeSistema' => 'SEI',
            'NomeSistemaComplemento' => '',
            'LogoMenu' => '',
            'Login' => true,
            'Ouvidoria' => true,
            'PublicacaoInterna' => true,
            'UsuariosExternos' => true,
            'ValidacaoDocumentos' => true,
            'ConsultaProcessual' => true
        ),

        'SessaoSEI' => array(
            'SiglaOrgaoSistema' => getenv('APP_ORGAO'),
            'SiglaSistema' => 'SEI',
            'PaginaLogin' => getenv('APP_PROTOCOLO').'://'.getenv('APP_HOST').'/sip/login.php',
            'SipWsdl' => getenv('APP_PROTOCOLO').'://'.getenv('APP_HOST').'/sip/controlador_ws.php?servico=sip',
          'ChaveAcesso' => getenv('APP_SEI_CHAVE_ACESSO'),
          'https' => (getenv('APP_PROTOCOLO') == 'https' ? true : false)),

 	      'BancoSEI'  => array(
 	          'Servidor' => getenv('APP_DB_HOST'),
 	          'Porta' => getenv('APP_DB_PORTA'),
 	          'Banco' => getenv('APP_DB_SEI_BASE'),
 	          'Usuario' => getenv('APP_DB_SEI_USERNAME'),
 	          'Senha' => getenv('APP_DB_SEI_PASSWORD'),
 	          'UsuarioScript' => getenv('APP_DB_SEI_USERNAME'),
 	          'SenhaScript' => getenv('APP_DB_SEI_PASSWORD'),
 	          'Tipo' => getenv('APP_DB_TIPO')), //MySql, SqlServer, Oracle ou PostgreSql

      /*
     'BancoAuditoriaSEI'  => array(
          'Servidor' => '[servidor BD]',
          'Porta' => '',
          'Banco' => '',
          'Usuario' => '',
          'Senha' => '',
          'Tipo' => ''), //MySql, SqlServer, Oracle ou PostgreSql
     */

      /*
     'BancoReplicaSEI'  => array(
          'Servidor' => '[servidor BD]',
          'Porta' => '',
          'Banco' => '',
          'Usuario' => '',
          'Senha' => '',
          'Tipo' => ''), //MySql, SqlServer, Oracle ou PostgreSql
     */

	'CacheSEI' => array('Servidor' => getenv('APP_MEMCACHED_HOST'),
			                	'Porta' => '11211'),

                                'Federacao' => array(
                                  'Habilitado' => (getenv('APP_FEDERACAO_HABILITAR') == 'true')
                                 ),

        'Manutencao' => array(
            'Ativada' => false,
            'Usuarios' => array('siglaUsuario1/siglaOrgao1', 'siglaUsuario2/siglaOrgao2'),
            'Mensagem' => 'Sistema em Manutenção',
            'Detalhes' => 'Previsão de retorno até as <b>XXhs.</b>'
        ),

        'hCaptcha' => array(
            'ChaveSecreta' => '',
            'ChaveSite' => ''
        ),

        'ReCaptchaV2' => array(
            'ChaveSecreta' => '',
            'ChaveSite' => ''
        ),

        'ReCaptchaV3' => array(
            'ChaveSecreta' => '',
            'ChaveSite' => '',
            'Score' => 0.5
        ),

        'JODConverter' => array('Servidor' => 'http://jod:8080/conversion?format=pdf'),

        'Solr' => array(
            'Servidor' => getenv('APP_SOLR_URL'),
            'Usuario' => 'sei',
            'Senha' => 'SolrSei123$',
            'CoreProtocolos' => getenv('APP_SOLR_CORE_PROTOCOLOS'),
            'TempoCommitProtocolos' => getenv('APP_SOLR_TEMPO_COMMIT_PROTOCOLOS'),
            'CoreBasesConhecimento' => getenv('APP_SOLR_CORE_BASECONHECIMENTO'),
            'CorePublicacoes' => getenv('APP_SOLR_CORE_PUBLICACOES'),
            'TempoCommitPublicacoes' => getenv('APP_SOLR_TEMPO_COMMIT_PUBLICACOES')
        ),

      'InfraMail' => array(
					'Tipo' => getenv('APP_MAIL_TIPO'), //1 = sendmail (neste caso nao e necessario configurar os atributos abaixo), 2 = SMTP
					'Servidor' => getenv('APP_MAIL_SERVIDOR'),
					'Porta' => getenv('APP_MAIL_PORTA'),
					'Codificacao' => getenv('APP_MAIL_CODIFICACAO'), //8bit, 7bit, binary, base64, quoted-printable
					'MaxDestinatarios' => getenv('APP_MAIL_MAXDESTINATARIOS'), //numero maximo de destinatarios por mensagem
					'MaxTamAnexosMb' => getenv('APP_MAIL_MAXTAMANHOANEXOSMB'), //tamanho maximo dos anexos em Mb por mensagem
					'Seguranca' => getenv('APP_MAIL_SEGURANCA'), //TLS, SSL ou vazio
					'Autenticar' => getenv('APP_MAIL_AUTENTICAR'), //se true entao informar Usuario e Senha
					'Usuario' => getenv('APP_MAIL_USUARIO'),
					'Senha' => getenv('APP_MAIL_SENHA'),
					'Protegido' => getenv('APP_MAIL_PROTEGIDO') //campo usado em desenvolvimento, se tiver um email preenchido entao todos os emails enviados terao o destinatario ignorado e substituido por este valor evitando envio incorreto de email
        )
    );
  }
}
?>