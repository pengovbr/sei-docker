<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
 *
 * 28/11/2017 - criado por mga
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class RelUsuarioGrupoAcompConfiguracaoDTO extends InfraDTO {

  public function getStrNomeTabela() {
    return null;
  }

  public function montar() {
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinAcompanhamentosZerados');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjRelUsuarioGrupoAcompDTO');
  }
}
?>