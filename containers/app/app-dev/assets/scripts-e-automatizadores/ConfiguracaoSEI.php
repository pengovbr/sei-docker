<?

class ConfiguracaoSEI extends InfraConfiguracao  {
	
	private static $instance = null;
	
	public static function getInstance(){
		if (ConfiguracaoSEI::$instance == null) {
			ConfiguracaoSEI::$instance = new ConfiguracaoSEI();
		}
		return ConfiguracaoSEI::$instance;
	}
	
	public function getArrConfiguracoes(){
		return array(
			'SEI' => array(
				'URL' => getenv('HOST_URL').'/sei',
                'Producao' => false,
                'DigitosDocumento' => 7,
                'PermitirAcessoLocalPdf' => '',
                'NumLoginUsuarioExternoSemCaptcha' => 3,
                'TamSenhaUsuarioExterno' => 8,
                'DebugWebServices' => 0,
                'RepositorioArquivos' => '/var/sei/arquivos',
                'Modulos' => array(
                    //'ABCExemploIntegracao' => 'abc/exemplo',
                    //'PesquisaIntegracao' => 'pesquisa',
                    //'WScomplementarIntegracao' => 'ws_complementar',
                    //'PeticionamentoIntegracao' => 'peticionamento',
                    //'RelacionamentoInstitucionalIntegracao' => 'relacionamento-institucional',
                    //'CorreiosIntegracao' => 'correios',
                    //'LitigiosoIntegracao' => 'litigioso',
                    //'UtilidadesIntegracao' => 'utilidades',
                    //'MdJulgarIntegracao' => 'trf4/julgamento',
                    //'MdWsSeiRest' => 'wssei',
                    //'PENIntegracao' => 'pen',
                ),
			),
			
			'SessaoSEI' => array(
				'SiglaOrgaoSistema' => 'ABC',
				'SiglaSistema' => 'SEI',
				'PaginaLogin' => getenv('HOST_URL') . '/sip/login.php',
				'SipWsdl' => getenv('HOST_URL') . '/sip/controlador_ws.php?servico=sip',
                'ChaveAcesso' => getenv('SEI_CHAVE_ACESSO'), //ATENCAO: gerar uma nova chave para o SEI apѓs a instalaчуo (ver documento de instalaчуo)
                'https' => false,
			),

			'PaginaSEI' => array(
				'NomeSistema' => 'SEI',
				'NomeSistemaComplemento' => SEI_VERSAO,
				'LogoMenu' => '',
				'OrgaoTopoJanela' => 'S',
			),

			'BancoSEI'  => array(
				'Servidor' => getenv('DATABASE_HOST'),
				'Porta' => getenv('DATABASE_PORT'),
				'Banco' => getenv('SEI_DATABASE_NAME'),
				'Usuario' => getenv('SEI_DATABASE_USER'),
				'Senha' => getenv('SEI_DATABASE_PASSWORD'),
				'Tipo' => getenv('DATABASE_TYPE'), //MySql, SqlServer ou Oracle
				'PesquisaCaseInsensitive' => false,
			),

//			'BancoAuditoriaSEI'  => array(
//                'Servidor' => getenv('DATABASE_HOST'),
//                'Porta' => getenv('DATABASE_PORT'),
//                'Banco' => getenv('SEI_DATABASE_NAME'),
//                'Usuario' => getenv('SEI_DATABASE_USER'),
//                'Senha' => getenv('SEI_DATABASE_PASSWORD'),
//                'Tipo' => getenv('DATABASE_TYPE'), //MySql, SqlServer ou Oracle
//                'PesquisaCaseInsensitive' => false,
//			),

			'CacheSEI' => array(
				'Servidor' => 'memcached',
				'Porta' => '11211',
				'Timeout' => 1,
				'Tempo' => 3600,					
			),

            'Federacao' => array(
                'Habilitado' => false,
                'NumSegundosAcaoRemota' => 10,  //Tempo mсximo que um link de aчуo do SEI Federaчуo pode ser executado.
                'NumSegundosSincronizacao' => 300,  //Diferenчa mсxima em segundos entre os horсrios das instalaчѕes.
                'NumDiasTentativasReplicacao' => 3,  //Informa por quanto tempo o sistema tentarс replicar sinalizaчѕes em processos para outras instalaчѕes do SEI Federaчуo.
                'ReplicarAcessosOnline' => true,  //Sinaliza se as concessѕes de acessos para ѓrgуos de outras instalaчѕes devem ser replicadas no mesmo instante. Se o valor for false ou se ocorrer um erro entуo as replicaчѕes serуo tratadas pelo agendamento de replicaчѕes.
                'NumMaxProtocolosConsulta' => 100,  //Nњmero mсximo de protocolos do processo que serуo retornados quando outra instituiчуo consultar pelo SEI Federaчуo (acima deste valor serс realizada paginaчуo).
                'NumMaxAndamentosConsulta' => 100,  //Nњmero mсximo de andamentos do processo que serуo retornados quando outra instituiчуo consultar pelo SEI Federaчуo (acima deste valor serс realizada paginaчуo).
            ),

            'XSS' => array(
                'NivelVerificacao' => 'A', //B=Bсsico, A=Avanчado, N=Nenhum
                'ProtocolosExcecoes' => null,
                'NivelBasico' => array(
                    'ValoresNaoPermitidos' => null,
                ),
                'NivelAvancado' => array(
                    'TagsPermitidas' => null,
                    'TagsAtributosPermitidos' => null,
                ),
            ),

            'Limites' => array(
                //Nэvel 1 щ afeto a Operaчѕes em geral
                'Nivel1TempoSeg' => 60,  //Esta chave define o Tempo mсximo em segundos para execuчуo do script.
                'Nivel1MemoriaMb' => 256,  //Esta chave define a Quantidade mсxima de memѓria em Megabytes que o script pode utilizar.
                //Nэvel 2 щ afeto a Download de documentos, Estatэsticas, Geraчуo de PDF, Migraчуo de Unidade, Indexaчуo Individual e Substituiчуo de contatos
                'Nivel2TempoSeg' => 600,  //Esta chave define o Tempo mсximo em segundos para execuчуo do script.
                'Nivel2MemoriaMb' => 2048,  //Esta chave define a Quantidade mсxima de memѓria em Megabytes que o script pode utilizar.
                //Nэvel 3 щ afeto a Scripts, Agendamentos, Indexaчуo Massiva, Critщrios de Controle Interno e Web Services
                'Nivel3TempoSeg' => 0,  //Esta chave define o Tempo mсximo em segundos para execuчуo do script. Este nэvel aceita o valor 0 para indicar sem limite de tempo.
                'Nivel3MemoriaMb' => 4096,  //Esta chave define a Quantidade mсxima de memѓria em Megabytes que o script pode utilizar. Este nэvel aceita o valor -1 para indicar sem limite de memѓria.
            ),

            'RH' => array(
                'CargoFuncao' => '',  //Endereчo para o serviчo de recuperaчуo de Cargos/Funчѕes para assinatura de documentos (opcional).
            ),

			'Solr' => array(
				'Servidor' => 'http://solr:8983/solr',
				'CoreProtocolos' => 'sei-protocolos',
				'CoreBasesConhecimento' => 'sei-bases-conhecimento',
				'CorePublicacoes' => 'sei-publicacoes',
				'TempoCommitProtocolos' => 300,
				'TempoCommitBasesConhecimento' => 60,
				'TempoCommitPublicacoes' => 60,					
			),				
			
			'JODConverter' => array(
				'Servidor' => 'http://jod:8080/converter/service'
			),

            'InfraMail' => array(
                'Tipo' => '2', //1 = sendmail (neste caso nуo щ necessсrio configurar os atributos abaixo), 2 = SMTP
                'Servidor' => 'smtp',
                'Porta' => '1025',
                'Codificacao' => '8bit', //8bit, 7bit, binary, base64, quoted-printable
                'Autenticar' => false, //se true entуo informar Usuario e Senha
                'Usuario' => '',
                'Senha' => '',
                'Seguranca' => '', //TLS, SSL ou vazio
                'MaxDestinatarios' => 25, //numero maximo de destinatarios por mensagem
                'MaxTamAnexosMb' => 15, //tamanho maximo dos anexos em Mb por mensagem
                'Protegido' => '', //campo usado em desenvolvimento, se tiver um email preenchido entao todos os emails enviados terao o destinatario ignorado e substituэdo por este valor (evita envio incorreto de email)
                /*  Abaixo chave opcional desativada com exemplo de preenchimento
                'Dominios' => array(	// Opcional. Permite especificar o conjunto de atributos acima individualmente para cada domэnio de conta remetente. Se nуo existir um domэnio mapeado entуo utilizarс os atributos gerais da chave InfraMail.
                    'abc.jus.br' => array(
                        'Tipo' => '2',
                        'Servidor' => '10.1.3.12',
                        'Porta' => '25',
                        'Codificacao' => '8bit',
                        'Autenticar' => false,
                        'Usuario' => '',
                        'Senha' => '',
                        'Seguranca' => 'TLS',
                        'MaxDestinatarios' => 25,
                        'MaxTamAnexosMb' => 15,
                        'Protegido' => '',
                        ),
                    ),
                    */
            ),
		);
	}
}
?>