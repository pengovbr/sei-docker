<?
/**
* Classe de acesso ao WebService da TJ-RS
*
* criado em 30/04/2008 - mauro_db
* 
* Observa��es:
* 1 - habilitado somente nos servidores "bohr" e "krebs"
* 2 - em todos os m�todos � obrigat�rio: usuarioTRF4, senhaTRF4 e sigla do usu�rio
* 3 - n�mero do processo atual: CCC/S.AA.NNNNNNN-D
*     onde: 
*     CCC - C�digo da comarca (tabela no banco siapro "comarca"
*     S - se��o do processo (1-processos c�veis, 2-processos criminais e JECrime, 3-processos JEC�vel)
*     AA - ano de cadastramento do processo
*     NNNNNNN - n�mero sequencial dentro do ano e se��o
*     D - d�gito de controle
*/

class InfraTJRS {
  
  private static $usuarioTRF4 = 'TRF4';
  private static $senhaTRF4 = 'rneui23iuc';
  private static $ws = 'https://www.tj.rs.gov.br/site_php/webservices/consultas-server.php?wsdl';
  private static $client = null;
  
	private function __construct() {
	  
  }

  private function setCliente() {
    try {
      if(!@file_get_contents(self::$ws)) {
        throw new SoapFault('Server', 'WSDL n�o encontrado em:' . self::$ws);
      }
      self::$client = new SoapClient(self::$ws, array('style'=> "mime", 'style' => SOAP_RPC, 'use' => SOAP_ENCODED, 'encoding' => 'ISO-8859-1'));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao setar o cliente: ' . $soapFault->faultstring;
    }
  }
  
  /**
   * Retorna partes em processos, a partir de uma busca fon�tica 
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pPagina: n�mero de p�ginas
   * @param Int $pTamanhoPagina: tamanho da p�gina [75 : 500]
   * @param String $pNomeParte: nome da parte
   * @param String $pSituacaoProcesso: situa��o dos processos [A - Ativos / B - Baixados]
   * @return Array('QUANTIDADE_NOMES', 'NOMES_PARTES' => Array('NOME', 'NOME_MAE', 'NOME_PAI', 'RG', 'COD_UF_RG', 'CPF', 'COD_GRAU_INSTRUCAO', 'COD_ESTADO_CIVIL', 'COD_REU', 'DATA_NASCIMENTO', 'SEQUENCIA', 'SEGREDO_JUSTICA'))
   */
  public static function consultarNomesPartes($pSiglaUsuario, $pCodComarca, $pPagina, $pTamanhoPagina, $pNomeParte, $pSituacaoProcesso) {
    try {
      self::setCliente();
      $respostaWS = self::$client->getNomesPartes(array('USUARIO' => self::$usuarioTRF4, 
                                                        'SENHA' => self::$senhaTRF4,
                                                        'MATRICULA' => $pSiglaUsuario,
                                                        'COD_COMARCA' => $pCodComarca,
                                                        'PAGINA' => $pPagina,
                                                        'TAMANHO_PAGINA' => $pTamanhoPagina,
                                                        'NOME_PARTE' => $pNomeParte,
                                                        'SITUACAO_PROCESSO' => $pSituacaoProcesso));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar nomes de partes foneticamente: ' . $soapFault->faultstring;
    }
    $arrRet = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrRet);$i++) {
      $arrTemp = $arrRet[$i]['NOMES_PARTES'];
      for ($j=0;$j<count($arrTemp);$j++) {
        $arrTemp[$j] = get_object_vars($arrTemp[$j]);
      }
      $arrRet[$i]['NOMES_PARTES'] = $arrTemp;
    }
    return $arrRet;
  }

  /**
   * Retorna os processos da parte informada
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Array $arrParte('COD_COMARCA', 'PAGINA', 'TAMANHO_PAGINA', 'NOME_PARTE', 'NOME_PAI', 'NOME_MAE', 'RG', 'COD_UF_RG', 'CPF', 'COD_GRAU_INSTRUCAO', 'COD_ESTADO_CIVIL', 'COD_REU', 'DATA_NASCIMENTO', 'SITUACAO_PROCESSO')
   * @return Array('QUANTIDADE_PARTES', 'PARTES' => Array('NOME', 'TIPO_PARTE', 'COD_COMARCA', 'NUMERO_PROCESSO', 'DATA_PROPOSITURA', 'NUMERO_ANTIGO', 'COD_REU', 'CLASSE', 'NATUREZA', 'SEGREDO_JUSTICA'))
   */
  public static function consultarProcessosParte($pSiglaUsuario, $arrParte) {
    try {
      if (!is_array($arrParte)) {
        throw new SoapFault('Client', 'O segundo par�metro n�o � um array.');
      }
      self::setCliente();
      $respostaWS = self::$client->getNomesPartes(array_merge(array('USUARIO' => self::$usuarioTRF4, 
                                                                    'SENHA' => self::$senhaTRF4,
                                                                    'MATRICULA' => $pSiglaUsuario),
                                                              $arrParte));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar nomes de partes foneticamente: ' . $soapFault->faultstring;
    }
    $arrRet = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrRet);$i++) {
      $arrTemp = $arrRet[$i]['PARTES'];
      for ($j=0;$j<count($arrTemp);$j++) {
        $arrTemp[$j] = get_object_vars($arrTemp[$j]);
      }
      $arrRet[$i]['PARTES'] = $arrTemp;
    }
    return $arrRet;
  }

  /**
   * Retorna os movimentos de um processo
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @param Int $pNumAntigo: n�mero de processo antigo (0 = ignora)
   * @return Array(Array('DATA', 'DESCRICAO', 'COMPLEMENTO', 'LINK_DESPACHO'))
   */
  public static function consultarMovimentosProcesso($pSiglaUsuario, $pCodComarca, $pNumProcesso, $pNumAntigo = '0') {
    try {
      self::setCliente();
      $respostaWS = self::$client->getMovimentosProcesso(array('USUARIO' => self::$usuarioTRF4, 
                                                            'SENHA' => self::$senhaTRF4,
                                                            'MATRICULA' => $pSiglaUsuario,
                                                            'COD_COMARCA' => $pCodComarca,
                                                            'NUMERO_PROCESSO' => $pNumProcesso,
                                                            'NUMERO_ANTIGO' => $pNumAntigo));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar movimentos de processo: ' . $soapFault->faultstring;
    }
    $arrTemp = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrTemp['MOVIMENTOS']);$i++) {
      $arrRet[] = get_object_vars($arrTemp['MOVIMENTOS'][$i]);
    }
    return $arrRet;
  }

  /**
   * Retorna as partes e seus advogados de um processo
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @param Int $pNumAntigo: n�mero de processo antigo (0 = ignora)
   * @return Array(('NOME', 'TIPO', 'ADVOGADOS' => Array('NOME', 'NUM_OAB', 'UF_OAB')))
   */
  public static function consultarPartesProcesso($pSiglaUsuario, $pCodComarca, $pNumProcesso, $pNumAntigo = '0') {
    try {
      self::setCliente();
      $respostaWS = self::$client->getPartesProcesso(array('USUARIO' => self::$usuarioTRF4, 
                                                            'SENHA' => self::$senhaTRF4,
                                                            'MATRICULA' => $pSiglaUsuario,
                                                            'COD_COMARCA' => $pCodComarca,
                                                            'NUMERO_PROCESSO' => $pNumProcesso,
                                                            'NUMERO_ANTIGO' => $pNumAntigo));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar movimentos de processo: ' . $soapFault->faultstring;
    }
    $arrTemp = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrTemp['PARTES']);$i++) {
      $arrRet[] = get_object_vars($arrTemp['PARTES'][$i]);
    }
    for ($i=0;$i<count($arrRet);$i++) {
      $arrTemp = $arrRet[$i]['ADVOGADOS'];
      for ($j=0;$j<count($arrTemp);$j++) {
        $arrTemp[$j] = get_object_vars($arrTemp[$j]);
      }      
      $arrRet[$i]['ADVOGADOS'] = $arrTemp;
    }
    return $arrRet;
  }
  
  /**
   * Retorna dados de um processo de 1� grau
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @param Int $pNumAntigo: n�mero de processo antigo (0 = ignora)
   * @return Array('NUMERO_PROCESSO', 'PROCESSO_FORMATADO', 'NUMERO_ANTIGO', 'VALOR_ACAO', 'SECAO', 'NUM_JUIZADO', 'NUM_JUDICANCIA', 'PROCESSO_PRINCIPAL', 'CLASSE', 'NATUREZA', 'SEGREDO_JUSTICA', 'QTDE_VOLUMES', 'ORGAO_JULGADOR', 'DATA_PROPOSITURA', 'LOCAL_AUTOS', 'SITUACAO_PROCESSO', 'DATA_ULTIMO_MOVIMENTO', 'ULTIMO_MOVIMENTO', 'DATA_ATUALIZACAO', 'DATA_BAIXA', 'COD_CLASSE', 'COD_NATUREZA', 'COD_ORGAO_JULGADOR', 'CODIGO_RESULTADO', 'MENSAGEM_RESULTADO')
   */
  public static function consultarDadosProcesso1Grau($pSiglaUsuario, $pCodComarca, $pNumProcesso, $pNumAntigo = '0') {
    try {
      self::setCliente();
      $respostaWS = self::$client->getDadosProcesso1G(array('USUARIO' => self::$usuarioTRF4, 
                                                            'SENHA' => self::$senhaTRF4,
                                                            'MATRICULA' => $pSiglaUsuario,
                                                            'COD_COMARCA' => $pCodComarca,
                                                            'NUMERO_PROCESSO' => $pNumProcesso,
                                                            'NUMERO_ANTIGO' => $pNumAntigo));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar dados de processos no 1� Grau: ' . $soapFault->faultstring;
    }
    return get_object_vars($respostaWS);
  }

  /**
   * Retorna processos de 1� grau relacionados ao processo de 1� grau informado
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @param Int $pNumAntigo: n�mero de processo antigo (0 = ignora)
   * @return Array('COD_COMARCA', 'NUMERO_PROCESSO', 'NUMERO_PROCESSO_FORMATADO')
   */
  public static function consultarProcessosVinculados1Grau($pSiglaUsuario, $pCodComarca, $pNumProcesso, $pNumAntigo = '0') {
    try {
      self::setCliente();
      $respostaWS = self::$client->getProcessosVinculados(array('USUARIO' => self::$usuarioTRF4, 
                                                                'SENHA' => self::$senhaTRF4,
                                                                'MATRICULA' => $pSiglaUsuario,
                                                                'COD_COMARCA' => $pCodComarca,
                                                                'NUMERO_PROCESSO' => $pNumProcesso,
                                                                'NUMERO_ANTIGO' => $pNumAntigo));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar processos de 1� Grau relacionados: ' . $soapFault->faultstring;
    }
    $arrTemp = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrTemp['PROCESSOS']);$i++) {
      $arrRet[] = get_object_vars($arrTemp['PROCESSOS'][$i]);
    }
    return $arrRet;
  }

  /**
   * Retorna as notas de expediente de um processo
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @param Int $pNumAntigo: n�mero de processo antigo (0 = ignora)
   * @return Array('ANO_COD_NOTA', 'DATA_NOTA', 'TEXTO')
   */
  public static function consultarNotasExpedienteProcesso($pSiglaUsuario, $pCodComarca, $pNumProcesso, $pNumAntigo = '0') {
    try {
      self::setCliente();
      $respostaWS = self::$client->getNotasExpedienteProcesso(array('USUARIO' => self::$usuarioTRF4, 
                                                                    'SENHA' => self::$senhaTRF4,
                                                                    'MATRICULA' => $pSiglaUsuario,
                                                                    'COD_COMARCA' => $pCodComarca,
                                                                    'NUMERO_PROCESSO' => $pNumProcesso,
                                                                    'NUMERO_ANTIGO' => $pNumAntigo));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar as notas de expediente: ' . $soapFault->faultstring;
    }
    $arrTemp = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrTemp['NOTAS']);$i++) {
      $arrRet[] = get_object_vars($arrTemp['NOTAS'][$i]);
    }
    return $arrRet;
  }


  /**
   * Retorna os n�meros de processos de 2� grau, originados de um processo de 1� grau
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pCodComarca: n�mero da comarca
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @param Int $pNumAntigo: n�mero de processo antigo (0 = ignora)
   * @return Array('NUMERO_PROCESSO')
   
  public static function consultarProcessos2G($pSiglaUsuario, $pCodComarca, $pNumProcesso, $pNumAntigo = '0') {
    try {
      self::setCliente();
      $respostaWS = self::$client->getProcessos2G(array('USUARIO' => self::$usuarioTRF4, 
                                                        'SENHA' => self::$senhaTRF4,
                                                        'MATRICULA' => $pSiglaUsuario,
                                                        'COD_COMARCA' => $pCodComarca,
                                                        'NUMERO_PROCESSO' => $pNumProcesso,
                                                        'NUMERO_ANTIGO' => $pNumAntigo));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar processos no 2� grau: ' . $soapFault->faultstring;
    }
    $arrTemp = get_object_vars($respostaWS);
    for ($i=0;$i<count($arrTemp['PROCESSOS']);$i++) {
      $arrRet[] = get_object_vars($arrTemp['PROCESSOS'][$i]);
    }
    return $arrRet;    
  }*/
  
  /**
   * Retorna dados de um processo de 2� grau
   *
   * @param String $pSiglaUsuario: sigla do usu�rio
   * @param Int $pNumProcesso: n�mero do processo SEM a comarca
   * @return Array('NUMERO_PROCESSO', 'SECAO', 'PROCESSO_PRINCIPAL', 'ORGAO_JULGADOR', 'CLASSE', 'RELATOR', 'NATUREZA', 'SEGREDO_JUSTICA', 'QTDE_VOLUMES', 'DATA_DISTRIBUICAO', 'DATA_ULTIMO_MOVIMENTO', 'ULTIMO_MOVIMENTO', 'SIGILO_ULTIMO_MOVIMENTO', 'DATA_ATUALIZACAO', 'DATA_BAIXA', 'DATA_PROPOSITURA', 'COD_CLASSE', 'COD_NATUREZA', 'COD_ORGAO_JULGADOR')
   
  public static function consultarDadosProcesso2Grau($pSiglaUsuario, $pNumProcesso) {
    try {
      self::setCliente();
      $respostaWS = self::$client->getDadosProcesso2G(array('USUARIO' => self::$usuarioTRF4, 
                                                            'SENHA' => self::$senhaTRF4,
                                                            'MATRICULA' => $pSiglaUsuario,
                                                            'NUMERO_PROCESSO' => $pNumProcesso));
    } catch (SoapFault $soapFault) {
      return 'ERRO ao consultar dados de processos no 2� Grau: ' . $soapFault->faultstring;
    }
    return get_object_vars($respostaWS);
  }*/
  
}
?>