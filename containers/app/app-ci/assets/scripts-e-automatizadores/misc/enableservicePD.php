<?php

/*
Cadastra o servico do protocolo digital
Verifique as variaveis abaixo com gentenv
*/

require_once '/opt/sei/web/SEI.php';

SessaoSEI::getInstance(false);
InfraDebug::getInstance()->setBolLigado(true);
InfraDebug::getInstance()->setBolDebugInfra(false);
InfraDebug::getInstance()->setBolEcho(true);

$conexao = BancoSEI::getInstance();
$conexao->setBolScript(true);

$SIGLA_SISTEMA=getenv('SERVICO_PD_SIGLA') ?: "GOV.BR";
$SERVICO_NOME=getenv('SERVICO_PD_NOME') ?: "Protocolo.GOV.BR";
$STR_OPERACOES=getenv('SERVICO_PD_OPERACOES') ?: "3,2,15,0,1";


$objUsuarioDTO = new UsuarioDTO();
$objUsuarioDTO->setNumIdUsuario(null);
$objUsuarioDTO->setNumIdOrgao(0);
$objUsuarioDTO->setStrIdOrigem(null);
$objUsuarioDTO->setStrSigla($SIGLA_SISTEMA);
$objUsuarioDTO->setStrNome($SIGLA_SISTEMA);
$objUsuarioDTO->setNumIdContato(null);
$objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SISTEMA);
$objUsuarioDTO->setStrSenha(null);
try{
$objUsuarioDTO->setStrSinAcessibilidade('N');
}catch(Exception $e){;}
$objUsuarioDTO->setStrSinAtivo('S');

$objUsuarioRN = new UsuarioRN();
$objUsuarioDTO = $objUsuarioRN->cadastrarRN0487($objUsuarioDTO);

$id_usuario=$objUsuarioDTO->getNumIdUsuario();

$objServicoDTO = new ServicoDTO();
$objServicoDTO->setNumIdServico(null);
$objServicoDTO->setNumIdUsuario($id_usuario);
$objServicoDTO->setStrIdentificacao($SERVICO_NOME);
$objServicoDTO->setStrDescricao($SERVICO_NOME);
$objServicoDTO->setStrServidor("*");
$objServicoDTO->setStrSinLinkExterno("S");
$objServicoDTO->setStrSinServidor("S");
$objServicoDTO->setStrSinChaveAcesso("N");
$objServicoDTO->setStrSinAtivo('S');

$objServicoRN = new ServicoRN();
$objServicoDTO = $objServicoRN->cadastrar($objServicoDTO);

$id_servico = $objServicoDTO->getNumIdServico();

$arrOperacoes = explode(",", $STR_OPERACOES);

foreach($arrOperacoes as $o){
    $objOperacaoServicoDTO = new OperacaoServicoDTO();
    $objOperacaoServicoDTO->setNumIdOperacaoServico(null);
    $objOperacaoServicoDTO->setNumIdServico($id_servico);
    $numStaOperacaoServico = $o;
    $objOperacaoServicoDTO->setNumStaOperacaoServico($numStaOperacaoServico);
    $objOperacaoServicoDTO->setNumIdTipoProcedimento(null);
    $objOperacaoServicoDTO->setNumIdSerie(null);
    $objOperacaoServicoDTO->setNumIdUnidade(null);
    $objOperacaoServicoRN = new OperacaoServicoRN();
    $objOperacaoServicoDTO = $objOperacaoServicoRN->cadastrar($objOperacaoServicoDTO);
}

?>