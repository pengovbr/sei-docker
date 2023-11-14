<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 23/08/2019 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.42.0
*/

require_once dirname(__FILE__).'/../SEI.php';

class GrupoBlocoINT extends InfraINT {

  public static function montarSelectUnidade($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){

    $objGrupoBlocoDTO = new GrupoBlocoDTO();
    $objGrupoBlocoDTO->retNumIdGrupoBloco();
    $objGrupoBlocoDTO->retStrNome();
    $objGrupoBlocoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objGrupoBlocoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objGrupoBlocoRN = new GrupoBlocoRN();
    $arrObjGrupoBlocoDTO = $objGrupoBlocoRN->listar($objGrupoBlocoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjGrupoBlocoDTO, 'IdGrupoBloco', 'Nome');
  }
}
