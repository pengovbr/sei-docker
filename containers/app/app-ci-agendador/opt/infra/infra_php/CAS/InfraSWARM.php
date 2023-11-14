<?php

require_once("InfraCasHttpClass.php");
require_once("InfraCasLifepoint.php");
require_once("InfraCasObject.php");
require_once("InfraCasNode.php");
require_once("InfraCasClusters.php");
require_once ("IInfraCAS.php");

define('CASTOR_OPER_READ',                     1001);
define('CASTOR_OPER_WRITE',                    1002);
define('CASTOR_OPER_DELETE',                   1003);
define('CASTOR_OPER_INFO',                     1004);

define('CASTOR_LOG_LEVEL_ERROR',                 10);
define('CASTOR_LOG_LEVEL_WARNING',               20);
define('CASTOR_LOG_LEVEL_INFO',                  30);
define('CASTOR_LOG_LEVEL_DEBUG',                 40);

define('CASTOR_ERROR_UNAVAILABLE_NODES_ERROR',  800);
define('CASTOR_ERROR_STREAM_ERROR',             801);
define('CASTOR_ERROR_TOO_MANY_RETRIES',         802);
define('CASTOR_ERROR_EXCEPTION',                803);
define('CASTOR_ERROR_NOT_FOUND',                804);
define('CASTOR_ERROR_UNSPECIFIED_ERROR',        899);


define('HTTP_CLIENT_ERROR_NO_ERROR',                 0);
define('HTTP_CLIENT_ERROR_INVALID_SERVER_ADDRESS',   700);
define('HTTP_CLIENT_ERROR_CANNOT_CONNECT',           701);
define('HTTP_CLIENT_ERROR_COMMUNICATION_FAILURE',    702);
define('HTTP_CLIENT_ERROR_CANNOT_ACCESS_LOCAL_FILE', 703);
define('HTTP_CLIENT_ERROR_PROTOCOL_FAILURE',         704);
define('HTTP_CLIENT_ERROR_INVALID_PARAMETERS',       705);
define('HTTP_CLIENT_ERROR_UNSPECIFIED_ERROR',        799);

/**
 * Classe com a funcionalidade de executar as opera��es de: leitura, escrita, informa��o e apagar objetos no Swarm.
 *
 * Exemplo de grava��o:
 *
 * $obj = new InfraCasObject(null, array(new InfraCasLifepoint(7,2,0), new InfraCasLifepoint(0,-1,-1, 1)));
 *
 * $obj->body="teste";
 *
 * $resultado=$swarm.salvarDocumento($obj);
 *
 *
 */
class InfraSWARM implements IInfraCAS
{
    public $maxretries = 3;
    public $error_code;
    public $error_message;

    public $cluster = null;
    public $maincluster = null;
    public $readcluster = null;
    public $selectedNode = null;
    public $leituraInicialNoMain = false;
    public $username = null;
    public $password = null;
    public $domain = null;

    /**
     * Construtor da classe contendo o cluster a ser utilizado para execu��o das transa��es.
     *
     * @param InfraCasCluster $clusters - Classe contendo os clusters para opera��o desta API.
     *
     * @see cas_clusters
     *
     **/
    function __construct($username, $password, $clusters) {
        $this->maincluster=$clusters->maincluster;
        $this->readcluster=$clusters->readcluster;
        $this->leituraInicialNoMain=$clusters->leituraInicialNoMain;
        $this->domain=$clusters->domain;
        $this->username=$username;
        $this->password=$password;
    }

    /**
     * Fun��o a ser implementada para gerar log de tempo de execu��o de cada requisi��o.
     *
     *
     * @param  int $operation       - Tipo de opera��o que est� sendo realizada, op��es.
     *                                CASTOR_OPER_READ, CASTOR_OPER_WRITE, CASTOR_OPER_INFO, CASTOR_OPER_DELETE
     *
     *                                Para obter uma representa��o textual da mensagem utilize InfraCasErrors::getCasOperationText($error_code)
     *
     * @param  float $timeinms      - Tempo de execu��o da opera��o em ms
     *
     * @param  InfraCasObject $obj       - Classe 'InfraCasObject' contendo informa��es sobre o objeto motivo deste 'log'
     *
     */
    function logTimer($operation, $timeinms, $obj) {

    }

    /**
     * Fun��o a ser implementada para gerar log do ocorrido nesta API.
     *
     * @param  int $loglevel        -  Indica o n�vel de informa��o associada a esta mensagem, op��es s�o.
     *                                CASTOR_LOG_LEVEL_ERROR, CASTOR_LOG_LEVEL_WARNING, CASTOR_LOG_LEVEL_INFO, CASTOR_LOG_LEVEL_DEBUG
     *
     *                                Para obter uma representa��o textual da mensagem utilize InfraCasErrors::getCasLevelText($error_code)
     *
     * @param  int $operation       - Tipo de opera��o que est� sendo realizada, op��es.
     *                                CASTOR_OPER_READ, CASTOR_OPER_WRITE, CASTOR_OPER_INFO, CASTOR_OPER_DELETE
     *
     *                                Para obter uma representa��o textual da mensagem utilize InfraCasErrors::getCasOperationText($error_code)
     *
     * @param  int $error_code      - C�digo do erro ou informa��o, caso o n�mero seja entre 700 e 800 trata-se de mensagens geradas por esta API nos demais casos s�o erros de HTTP.
     *
     *                                Para obter uma representa��o textual da mensagem utilize InfraCasErrors::getCasErrorText($error_code)
     *
     * @param  string $error_message - Mensagem textual do ocorrido
     * @param  InfraCasObject $obj       - Classe 'InfraCasObject' contendo informa��es sobre o objeto motivo deste 'log'
     *
     * @see cas_object
     *
     */
    public function logError($loglevel, $operation, $error_code, $error_message, $obj) {
    }

    /**
     * Fun��o para armazenar os dados em ambiente de cache, caso n�o deseje utilizar esta caracteristica basta n�o implementar os m�todos.
     *
     * @return bool true, se foi bem sucedida

     **/
    public function saveDataToCache($key, $data) {
    }

    /**
     * Fun��o para recuperar os dados em ambiente de cache, caso n�o deseje utilizar esta caracteristica basta n�o implementar os m�todos.
     *
     * @return bool true, se foi bem sucedida

     **/
    public function readDataFromCache($key) {
        return null;
    }

    // M�todos para uso interno
    private function setError($loglevel, $operation, $error_code, $error_message, $obj) {
        $this->error_code=$error_code;
        $this->error_message=$error_message;

        $this->logError($loglevel, $operation, $error_code, $error_message, $obj);

        return false;
    }
    private function clearError() {
        $this->error_code=0;
        $this->error_message="";
    }
    private function __writeInit(&$obj) {
        $this->cluster=$this->maincluster;

        $this->selectedNode=$this->cluster->getNode();
        if ($this->selectedNode==null)
            return $this->setError(CASTOR_LOG_LEVEL_ERROR, CASTOR_OPER_WRITE, CASTOR_ERROR_UNAVAILABLE_NODES_ERROR, "N�o existem n�s dispon�veis para gravar o objeto.", $obj );
    }
    private function __readInit(&$obj) {
        $this->cluster = $this->readcluster==null ? $this->maincluster :( $this->leituraInicialNoMain? $this->maincluster: $this->readcluster);

        $this->selectedNode=$this->cluster->getNode();
        if ($this->selectedNode==null) {
            if ( $this->readcluster==null)
                return $this->setError(CASTOR_LOG_LEVEL_ERROR, CASTOR_OPER_READ, CASTOR_ERROR_UNAVAILABLE_NODES_ERROR, "N�o foi poss�vel obter um n� para gravar objeto. (write)", $obj );

            if ($this->$leituraInicialNoMain)
                $this->cluster=$this->readcluster;
            else
                $this->cluster=$this->maincluster;
            $this->selectedNode=$this->cluster->getNode();
            if ($this->selectedNode==null)
                return $this->setError(CASTOR_LOG_LEVEL_ERROR, CASTOR_OPER_READ, CASTOR_ERROR_UNAVAILABLE_NODES_ERROR, "N�o foi poss�vel obter um n� para gravar objeto (read/write).", $obj );
        }

        return true;
    }

    private function core_process($operation, InfraCasObject &$obj)  {

        try {
            $this->clearError();
            $writeMode = false;
            switch($operation) {
                case CASTOR_OPER_DELETE:
                case CASTOR_OPER_WRITE:
                    $writeMode = true;
                    $result=$this->__writeInit($obj);
                    break;
                case CASTOR_OPER_INFO:
                case CASTOR_OPER_READ:
                    $result=$this->__readInit($obj);
                    break;
                default:
                    $result=true;
                    break;
            }

            $retries = $this->maxretries;
            do {
                //inicializar argumentos
                $sw = microtime(true);
                if ($obj->core_process_request($this->username, $this->password, $operation, $this->selectedNode->url, $this->domain)) {
                    $this->logTimer($operation, round((microtime(true) - $sw)*1000, 3), $obj);
                    return true;
                }

                $this->error_code=$obj->error_code;
                $this->error_message=$obj->error_message;

                if ($this->error_code==404 || $this->error_code == 701 || $this->error_code == 702) {
                    $this->logError(CASTOR_LOG_LEVEL_INFO, $operation, $this->error_code, "Objeto n�o localizado no endere�o ".$this->selectedNode->url, $obj);

                    if ($this->leituraInicialNoMain && !$writeMode)
                        $this->cluster=$this->readcluster;
                    else
                        $this->cluster=$this->maincluster;

                    $this->selectedNode=$this->cluster->getNode();
                    if ($this->selectedNode==null)
                        return $this->setError(CASTOR_LOG_LEVEL_ERROR, $operation, CASTOR_ERROR_UNAVAILABLE_NODES_ERROR, "N�o foi poss�vel obter um n� (read/write).", $obj );

                    $this->logError(CASTOR_LOG_LEVEL_INFO, $operation, $this->error_code, "Tentando localizador objeto no endere�o ".$this->selectedNode->url, $obj);
                    continue;
                } else {
                    if ( $this->error_code!=HTTP_CLIENT_ERROR_CANNOT_CONNECT) {
                        $this->logError(CASTOR_LOG_LEVEL_ERROR, $operation, $this->error_code, $this->error_message, $obj);
                        return false;
                    }
                    $this->logError(CASTOR_LOG_LEVEL_INFO, $operation, $this->error_code, "N� ".$this->selectedNode->url." n�o responde", $obj);
                }

                $this->selectedNode->fail();
                if ($this->cluster==$this->readcluster)
                    $this->saveDataToCache("cluster_read", $this->readcluster);
                else
                    if ($this->cluster==$this->maincluster)
                        $this->saveDataToCache("cluster_main", $this->maincluster);
                $this->selectedNode = $this->cluster->getNode();
                if ( $this->selectedNode==null) {
                    if (( $this->cluster==$this->readcluster) && ( !$this->leituraInicialNoMain)) {
                        $this->cluster=$this->maincluster;
                        $this->selectedNode=$this->cluster->getNode();
                        $this->logError(CASTOR_LOG_LEVEL_INFO, $operation, $this->error_code, "N�o foi poss�vel se conectar no cluster de leitura, mudando para cluster principal", $obj);
                    } else
                        if (( $this->cluster==$this->maincluster) && ( $this->leituraInicialNoMain) && ($operation!=CASTOR_OPER_WRITE) && ($operation!=CASTOR_OPER_DELETE)) {
                            $this->cluster=$this->readcluster;
                            $this->selectedNode=$this->cluster->getNode();
                            $this->logError(CASTOR_LOG_LEVEL_INFO, $operation, $this->error_code, "N�o foi poss�vel se conectar no cluster principal, mudando para cluster de leitura", $obj);
                        }
                    if ( $this->selectedNode==null)
                        return $this->setError(CASTOR_LOG_LEVEL_ERROR, $operation, CASTOR_ERROR_UNAVAILABLE_NODES_ERROR, "N�o existem n�s dispon�veis para gravar o objeto.", $obj );
                }
            } while (--$retries>0);
            return $this->setError(CASTOR_LOG_LEVEL_ERROR, $operation, CASTOR_ERROR_TOO_MANY_RETRIES, "Erro de grava��o, foram feitas um total de ".$this->maxretries." tentativas sem sucesso", $obj );
        }
        catch (Exception $e) {
            return $this->setError(CASTOR_LOG_LEVEL_ERROR, $operation, CASTOR_ERROR_EXCEPTION, "EXCEPTION: ".$e->getMessage(), $obj);
        }
    }

    // ====================================================================================================
    // Fun��es p�blicas
    // ====================================================================================================
    
    /**
     * Fun��o para armazenar objeto no sistema de armazenamento Swarm
     * @param InfraCasObject $obj - Classe contendo informa��es sobre o objeto a ser gravado
     * @return bool true, se foi bem sucedida, caso contrario retorna false e a mensagem e c�digos de erro est�o dentro do objeto
     *
     * @see cas_object
     **/
    public function salvarDocumento(InfraCasObject &$obj)  {
        return $this->core_process(CASTOR_OPER_WRITE, $obj);
    }
    
    /**
     * Fun��o para ler objeto do sistema de armazenamento Swarm
     * @param InfraCasObject $obj - Classe contendo o UUID do objeto a ser recuperado
     * @return bool true, se foi bem sucedida, caso contrario retorna false e a mensagem e c�digos de erro est�o dentro do objeto
     *
     * @see cas_object
     **/
    public function recuperarDocumento(InfraCasObject &$obj)  {
        return $this->core_process(CASTOR_OPER_READ, $obj);
    }
    
    /**
     * Fun��o para ler apenas o cabe�alho do objeto armazenado no Swarm
     * @param InfraCasObject $obj - Classe contendo o UUID do objeto a ser recuperado
     * @return bool true, se foi bem sucedida, caso contrario retorna false e a mensagem e c�digos de erro est�o dentro do objeto
     *
     * @see cas_object
     **/
    public function infoDocumento(InfraCasObject &$obj)  {
        return $this->core_process(CASTOR_OPER_INFO, $obj);
    }
    
    /**
     * Fun��o para apagar objeto do sistema de armazenamento Swarm
     * @param InfraCasObject $obj - Classe contendo o UUID do objeto a ser apagado
     * @return bool true, se foi bem sucedida, caso contrario retorna false e a mensagem e c�digos de erro est�o dentro do objeto
     *
     * @see cas_object
     **/
    public function apagarDocumento(InfraCasObject &$obj)  {
        return $this->core_process(CASTOR_OPER_DELETE, $obj);
    }
}