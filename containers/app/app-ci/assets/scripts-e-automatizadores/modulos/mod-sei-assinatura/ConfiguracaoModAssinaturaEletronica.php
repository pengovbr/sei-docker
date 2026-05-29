<?php

/**
 * Arquivo de configuração do Módulo de Integração do SEI com a plataforma de Assinatura Avançada do gov.br
 *
 * Seu desenvolvimento seguiu os mesmos padrões de configuração implementado pelo SEI e SIP e este
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
              // Endereço para acesso à API do serviço de assinatura avançada.
              //Os endereços disponíveis são os seguintes (verifique se houve atualizações durante o procedimento de instalação):
              //     - Homologação: https://cas.staging.iti.br/oauth2.0
              //     - Produção: https://cas.iti.br/oauth2.0
              'url_provider' => getenv('MODULO_ASSINATURA_URLPROVIDER'),

              // Chave de acesso, que identifica a aplicação consumidora devidamente autorizada para acessar os serviços de assinatura eletrônica.
              'client_id' => getenv('MODULO_ASSINATURA_CLIENTID'),

              // Senha de acesso da aplicação consumidora.
              'secret'    => getenv('MODULO_ASSINATURA_SECRET'),

          ),
          'ValidarAPI' => array(
             // Endereço para acesso à API do serviço Validar.
             // Os endereços disponíveis são os seguintes (verifique se houve atualizações durante o procedimento de instalação):
             //     - Homologação: https://h-api.iti.gov.br/validar/v3/validar
            //      - Produção: https://api.iti.gov.br/validar/v3/validar
            'url' => getenv('MODULO_ASSINATURA_VALIDAR_API_URL'),
            // Chave de acesso da API.
            'key' => getenv('MODULO_ASSINATURA_VALIDAR_API_KEY'),
          ),
          'Assinador' => array(
            'config' => array(
                // OID da suíte criptográfica a ser utilizada para assinatura.
                'suite' => '1.2.840.113549.1.1.11', // algoritmo sha256WithRSA.
            ),
            'Pkcs12' => array(
                // Endereço para acesso à API do serviço IntegraICP.
                'url' => getenv('MODULO_ASSINATURA_PKCS12_URL'),
                // URL pra enviar arquivo a ser assinado.
                'sign_url' => getenv('PKCS12_URL_ASSINAR'),
            ),
            'Ykue' => array(
                // Endereço para acesso à API do serviço Ykue.
                'url' => getenv('MODULO_ASSINATURA_YKUE_URL'),
                // URL pra enviar arquivo a ser assinado.
                'sign_url' => getenv('MODULO_ASSINATURA_YKUE_URL_ASSINAR'),
            ),
            'IntegraICP' => array(
                // Endereço para acesso à API do serviço IntegraICP.
                'url' => getenv('MODULO_ASSINATURA_INTEGRA_ICP_URL'),
                // URL pra retornar lista de autenticações do CPF.
                'clearings_url' => getenv('MODULO_ASSINATURA_INTEGRA_ICP_URL_CLEARINGS'),
                // URL pra enviar arquivo a ser assinado.
                'sign_url' => getenv('MODULO_ASSINATURA_INTEGRA_ICP_URL_ASSINAR'),
            ),
            'CloudPSC' => array(
              // Endereço para acesso à API do serviço Cloud PSC.
              'url' => getenv('MODULO_ASSINATURA_CLOUDPSC_URL'),
              // URL pra retornar lista de autenticações.
              'start_url' => getenv('MODULO_ASSINATURA_CLOUDPSC_URL_START'),
              // URL pra enviar arquivo a ser assinado.
              'sign_url' => getenv('MODULO_ASSINATURA_CLOUDPSC_URL_ASSINAR'),
              // Opções de assinadores do Cloud PSC ['safeweb', 'soluti', 'govbr', 'serpro'],
              'options' =>  ['govbr', 'serpro'],
            ),
            'apikey' => getenv('MODULO_ASSINATURA_API_KEY_ITYHY'),
          )
      );
  }
}