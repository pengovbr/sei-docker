<?php

/**
 * Arquivo de configuração do Módulo de Integração do SEI com a plataforma de Assinatura Avançada do gov.br
 *
 * Seu desenvolvimento seguiu os mesmos padrÃµes de configuração implementado pelo SEI e SIP e este
 * arquivo precisa ser adicionado à pasta de configurações do SEI para seu correto carregamento pelo módulo.
 */

class ConfiguracaoModAssinaturaEletronica extends InfraConfiguracao
{
  private static $instance = null;

    /**
     * Obtém instância única (singleton) dos dados de configuração do módulo de integração com a Conta gov.br
     *
     * @return ConfiguracaoModAssinaturaEletronica
     */
  public static function getInstance()
    {
    if (ConfiguracaoModAssinaturaEletronica::$instance == null) {
        ConfiguracaoModAssinaturaEletronica::$instance = new ConfiguracaoModAssinaturaEletronica();
    }
      return ConfiguracaoModAssinaturaEletronica::$instance;
  }

    /**
     * Definição dos parâmetros de configuração do módulo
     *
     * @return array
     */
  public function getArrConfiguracoes()
    {
      return array(
          'AssinaturaAvancada' => array(
              'url_provider' => getenv('MODULO_ASSINATURA_URLPROVIDER'),
              'client_id' => getenv('MODULO_ASSINATURA_CLIENTID'),
              'secret' => getenv('MODULO_ASSINATURA_SECRET'),
          ),
          'ValidarAPI' => array(
            'url' => getenv('MODULO_ASSINATURA_VALIDAR_API_URL'),
            'key' => getenv('MODULO_ASSINATURA_VALIDAR_API_KEY'),
          ),
          'Assinador' => array(
            'Token' => array(
                'url' => getenv('MODULO_ASSINATURA_TOKEN_URL'),
                'sign_url' => getenv('MODULO_ASSINATURA_TOKEN_SIGN_URL'),
            ),
            'IntegraICP' => array(
                'url' => getenv('MODULO_ASSINATURA_INTEGRA_ICP_URL'),
                'clearings_url' => getenv('MODULO_ASSINATURA_INTEGRA_ICP_URL_CLEARINGS'),
                'sign_url' => getenv('MODULO_ASSINATURA_INTEGRA_ICP_URL_ASSINAR'),
            ),
            'CloudPSC' => array(
              'url' => getenv('MODULO_ASSINATURA_CLOUDPSC_URL'),
              'start_url' => getenv('MODULO_ASSINATURA_CLOUDPSC_START_URL'),
              'sign_url' => getenv('MODULO_ASSINATURA_CLOUDPSC_SIGN_URL'),
              'options' =>  ['govbr', 'serpro'],
            ),
            'apikey' => getenv('MODULO_ASSINATURA_API_KEY_ITYHY'),
          )
      );
  }
}