<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
 *
 * 05/04/2018 - criado por MGA
 *
 * @package infra_php
 */


class InfraXSS {

  private $arrImagens = array();
  private $strDiferenca = '';
  private $bolProcessarXML = false;

  /**
   * @return bool
   */
  public function isBolProcessarXML()
  {
    return $this->bolProcessarXML;
  }

  /**
   * @param bool $bolProcessarXML
   */
  public function setBolProcessarXML($bolProcessarXML)
  {
    $this->bolProcessarXML = $bolProcessarXML;
  }


  public function __construct(){

  }

  public function getStrDiferenca(){
    return $this->strDiferenca;
  }

  public function verificacaoBasica($strConteudo, $arrValoresNaoPermitidos = null){

    $ret = null;

    if ($arrValoresNaoPermitidos == null) {
      $arrValoresNaoPermitidos = array('XMLHttpRequest',
          'setRequestHeader',
          'onload',
          'decodeURIComponent',
          'document.cookie',
          'document.write',
          'parentNode',
          'innerHTML',
          'appendChild');
    }

    $strConteudoVerificacao = strtolower($strConteudo);
    foreach($arrValoresNaoPermitidos as $strNaoPermitido) {
      if (strpos($strConteudoVerificacao, strtolower(trim($strNaoPermitido))) !== false) {

        if ($ret == null){
          $ret = array();
        }

        $ret[] = $strNaoPermitido;
      }
    }

    return $ret;
  }

  public function verificacaoAvancada(&$strConteudo, $arrTagsPermitidas=null, $arrTagsAtributosPermitidos=null, $bolDiferenca = true){


    if ($arrTagsPermitidas === null){
      $arrTagsPermitidas = array('html', 'body', 'head', 'style', 'meta', 'link', 'title', 'input');
    }

    if ($arrTagsAtributosPermitidos === null){
      $arrTagsAtributosPermitidos = array('style');
    }


    $objAntiXSS = new voku\helper\AntiXSS();
    $objAntiXSS->removeEvilHtmlTags($arrTagsPermitidas);
    $objAntiXSS->removeEvilAttributes($arrTagsAtributosPermitidos);
    //$objAntiXSS->setReplacement('[removed]');

    InfraDebug::getInstance()->gravarInfra('InfraXSS: Iniciar parse do DOM');
    if($this->bolProcessarXML){
      $dom = InfraHTML::parseXml($strConteudo);
    } else {
      $dom = InfraHTML::parseHtml($strConteudo);
    }
    if($dom) {
      InfraDebug::getInstance()->gravarInfra('InfraXSS: Remover text nodes');
      InfraHTML::removerTextNodes($dom);
      if($this->bolProcessarXML){
        $strConteudoSemTexto = $dom->saveXML();
      } else{
        $strConteudoSemTexto = $dom->saveHTML();
      }
      $objAntiXSS->setXssDiffProcessing(false);
      $result=$this->verificacaoAvancadaInterno($strConteudoSemTexto,$objAntiXSS);
      if ($result===false) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: DOM sem XSS. Retornando OK;');
        return false;
      }
    } else {
      InfraDebug::getInstance()->gravarInfra('InfraXSS: Parse do DOM sem sucesso.');
    }
    InfraDebug::getInstance()->gravarInfra('InfraXSS: Avaliando conte�do completo.');
    $objAntiXSS->setXssDiffProcessing($bolDiferenca);
    return $this->verificacaoAvancadaInterno($strConteudo,$objAntiXSS);
  }

  private function verificacaoAvancadaInterno(&$strConteudo,$objAntiXSS){
    //exclui tag <!DOCTYPE >
    $count=0;
    $bolDebug=InfraDebug::isBolProcessar();

    while(($posIni=strpos($strConteudo,'<!DOCTYPE'))!==false){
      $posFechaTag=strpos($strConteudo,'>',$posIni);
      if($posFechaTag!==false){
        $strConteudo=substr_replace($strConteudo,'',$posIni,$posFechaTag-$posIni+1);
        ++$count;
      }
    }
    if($bolDebug){
      InfraDebug::getInstance()->gravarInfra('InfraXSS: DOCTYPE removido - '.$count);
      $strBackup=$strConteudo;
    }

    $strConteudo = str_replace('<!--/*--><![CDATA[/*><!--*/','',$strConteudo);
    if($bolDebug){
      if($strBackup!=$strConteudo){
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removido comentario CDATA ');
      }
      $strBackup=$strConteudo;
    }
    $strConteudo = str_replace('/*]]>*/-->','',$strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removido fim comentario CDATA ');
      }
      $strBackup = $strConteudo;
    }
    $strConteudo = str_replace('<!--[if-->','',$strConteudo);
    if($bolDebug){
      if($strBackup!=$strConteudo){
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removido comentario condicional ');
      }
      $strBackup=$strConteudo;
    }



    $strConteudo = str_replace('href="javascript:void(0);"','',$strConteudo);
    $strConteudo = str_replace('href="javascript:void(0)"','',$strConteudo);
    $strConteudo = str_replace('href="javascript:;"','',$strConteudo);
    $strConteudo = str_replace('href="javascript:"','',$strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removido href=javascript ');
      }
      $strBackup = $strConteudo;
    }

    $strConteudo = str_replace('xmlns="http://www.w3.org/1999/xhtml"','',$strConteudo);
    $strConteudo = str_replace('xmlns="http://www.w3.org/TR/REC-html40"','',$strConteudo);
    $strConteudo = str_replace('<?xml version="1.0"?>','',$strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removido xmlns/xml version ');
      }
      $strBackup = $strConteudo;
    }

    //substitui sequencia de espacos maior que 4 por um unico espaco
    $strConteudo = preg_replace('/\s{5,}/', ' ', $strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removida sequencia de espa�os');
      }
      $strBackup = $strConteudo;
    }


    //remove href de telefones
    $strConteudo = preg_replace_callback('#href="callto:([^"]*)"#', array($this,'validarTelefone'),$strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removido href Telefone ');
      }
      $strBackup = $strConteudo;
    }


    //retirar imagens base64 antes do filtro
    $strConteudo = preg_replace_callback('#data:\s*image/[a-z\-\+]+\s*;base64,[a-zA-Z0-9\/\+]*=*#',
        array($this,'substituirConteudoHash'),$strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: tratados BASE64 ');
      }
      $strBackup = $strConteudo;
    }

    //remove comentarios condicionais
    while(($posIniComentario=strpos($strConteudo,'<!--[if '))!==false){
      if(($posFinalComentario=strpos($strConteudo,'endif]-->',$posIniComentario))===false){
        break;
      }
      $strConteudo=substr_replace($strConteudo,'',$posIniComentario,$posFinalComentario-$posIniComentario+9);
    }
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removidos comentarios condicionais ');
      }
      $strBackup = $strConteudo;
    }

    //remove comentarios simples
    $strConteudo = preg_replace('/<!--([\s\S]*?)-->/', '', $strConteudo);
    if($bolDebug) {
      if ($strBackup!=$strConteudo) {
        InfraDebug::getInstance()->gravarInfra('InfraXSS: removidos comentarios simples ');
      }
      $strBackup = $strConteudo;
    }


    $strConteudo = $objAntiXSS->xss_clean($strConteudo);

    //recolocar imagens base64 ap�s filtro
    $strConteudo = preg_replace_callback('#data-infra-hash-([a-f0-9]{32}).jpg#',
        array($this,'substituirHashConteudo'),$strConteudo);

    //ini_set('default_charset','ISO-8859-1');

    if ($objAntiXSS->isXssFound()) {
      $this->strDiferenca=$objAntiXSS->getXssDiff();
      return true;
    }

    return false;
  }
  private function substituirConteudoHash($match){
    $strHash=hash('md5',$match[0]);
    $this->arrImagens[$strHash]=$match[0];
    return 'data-infra-hash-'.$strHash.'.jpg';
  }

  private function substituirHashConteudo($match){
    return $this->arrImagens[$match[1]];
  }

  private function validarTelefone($match){
    $str=urldecode($match[1]);
    if (preg_match('/[\(\)0-9\-+ ]*/',$str)===1){
      return 'href=""';
    }
    return $match[0];
  }

  public static function prepararTexto($str){
    $objAntiXSS = new voku\helper\AntiXSS();
    return $objAntiXSS->prepareText($str);
  }
}
?>