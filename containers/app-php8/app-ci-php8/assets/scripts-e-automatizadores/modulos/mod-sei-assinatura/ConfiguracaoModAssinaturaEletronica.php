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
              'url_provider' => getenv('ASSINATURA_URL_PROVIDER'),
              'client_id' => getenv('ASSINATURA_CLIENT_ID'),
              'secret' => getenv('ASSINATURA_SECRET'),
          ),
          'ValidarAPI' => array(
            'url' => getenv('VALIDAR_API_URL'),
            'key' => getenv('VALIDAR_API_KEY'),
          ),
          'Assinador' => array(
            'config' => array(
                'suite' => getenv('ASSINATURA_SUITE'),
            ),
            'Pkcs12' => array(
                'url' => getenv('PKCS12_URL'),
                'sign_url' => getenv('PKCS12_URL_ASSINAR'),
            ),
            'Ykue' => array(
                'url' => getenv('YKUE_URL'),
                'sign_url' => getenv('YKUE_URL_ASSINAR'),

            ),
            'IntegraICP' => array(
                'url' => getenv('INTEGRA_ICP_URL'),
                'clearings_url' => getenv('INTEGRA_ICP_URL_CLEARINGS'),
                'sign_url' => getenv('INTEGRA_ICP_URL_ASSINAR'),
            ),
            'CloudPSC' => array(
              'url' => getenv('CLOUD_PSC_URL'),
              'start_url' => getenv('CLOUD_PSC_URL_START'),
              'sign_url' => getenv('CLOUD_PSC_URL_ASSINAR'),
              'options' =>  ['govbr', 'serpro'],
              // 'options' =>  ['safeweb', 'soluti', 'govbr', 'serpro'],
            ),
            'apikey' => getenv('API_KEY_ITYHY'),
          )
      );
  }
}