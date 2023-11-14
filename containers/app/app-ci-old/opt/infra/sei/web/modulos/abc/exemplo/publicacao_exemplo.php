<?

try {

  require_once dirname(__FILE__).'/../../../SEI.php';

	session_start();
	
	//////////////////////////////////////////////////////////////////////////////
	InfraDebug::getInstance()->setBolLigado(false);
	InfraDebug::getInstance()->setBolDebugInfra(false);
	InfraDebug::getInstance()->limpar();
	//////////////////////////////////////////////////////////////////////////////

	SessaoPublicacoes::getInstance()->validarLink();
	
	SessaoPublicacoes::getInstance()->validarPermissao($_GET['acao']);

	switch($_GET['acao']){

	  case 'md_abc_publicacao_exemplo':

	    $strTitulo = 'Publica��o ABC';

	    break;

	  default:
	    throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
	}


} catch(Exception $e) { 
	PaginaPublicacoes::getInstance()->processarExcecao($e);
}

PaginaPublicacoes::getInstance()->montarDocType();
PaginaPublicacoes::getInstance()->abrirHtml();
PaginaPublicacoes::getInstance()->abrirHead();
PaginaPublicacoes::getInstance()->montarMeta();
PaginaPublicacoes::getInstance()->montarTitle('SEI - Publica��es Eletr�nicas');
PaginaPublicacoes::getInstance()->montarStyle();
PaginaPublicacoes::getInstance()->abrirStyle();
?>

/* CSS */

<?
PaginaPublicacoes::getInstance()->fecharStyle();
PaginaPublicacoes::getInstance()->montarJavaScript();
PaginaPublicacoes::getInstance()->abrirJavaScript();
?>

function inicializar(){
}

function onSubmitForm(){
  return true;
}

<?
PaginaPublicacoes::getInstance()->fecharJavaScript();
PaginaPublicacoes::getInstance()->fecharHead();
PaginaPublicacoes::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPublicacaoExemplo" method="post" onsubmit="return onSubmitForm();" action="<?=SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
PaginaPublicacoes::getInstance()->montarBarraComandosSuperior($arrComandos);
?>
</form>
<?
PaginaPublicacoes::getInstance()->montarAreaDebug();
PaginaPublicacoes::getInstance()->fecharBody();
PaginaPublicacoes::getInstance()->fecharHtml();
?>