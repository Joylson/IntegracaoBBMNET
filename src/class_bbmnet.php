<?php

/**
 * Classe de integração para o BBMNET
 * @author Diego Maiochi <diego@maiochi.eti.br>
 */
class Bbmnet {
    
    private $exception;
    private $trace;
    private $config;
    
    /* @var SoapClient */
    private $Cliente;

    public function __construct() {
        $this->carregaConfig();
        $this->exception = false;
        $this->trace     = false;
    }

    private function carregaConfig() {
        $this->config = parse_ini_file(__DIR__.'/config.ini');
    }
    
    public function setExibeExcetptions($bExibe) {
        $this->exception = $bExibe;
    }
    
    public function setExibeTrace($bExibe) {
        $this->trace = $bExibe;
    }

    /**
     * Cria, caso não tenha sido criado, o cliente com os parâmetros necessários para conexão
     * @return SoapClient
     */
    public function getCliente() {
        if(!isset($this->Cliente)) {
            $aParametros = $this->getParametrosConexao();
            $this->Cliente = new SOAPClient($this->config['ws_url'], $aParametros);
            $this->addHeaders();
        }

        return $this->Cliente;
    }

    /**
     * Cria os parâmetros que serão utilizados para a conexão com o Web Service
     * @return array;
     */
    private function getParametrosConexao() {
//        
//        $aOptions = Array(
//					'ssl' => Array(
//						 'verify_peer'       => false
//						,'allow_self-signed' => true
//						,'ciphers'           => 'TLSv1.2'
//					)
//					,'https' => Array(
//						 'curl_verify_ssl_peer' => false
//						,'curl_verify_ssl_host' => false
//					)
//				);
        
        $aParametros = ['cache_wsdl'     => WSDL_CACHE_NONE, 
                        'encoding'       => 'ISO-8859-1', 
                        'exceptions'     => $this->exception, 
                        'trace'          => $this->trace, 
                        //'stream_context' => stream_context_create($aOptions),
                        /*'proxy_host'     => $this->config['proxy_host'],
                        'proxy_port'     => $this->config['proxy_porta'],
                        'proxy_login'    => $this->config['proxy_login'],
                        'proxy_password' => $this->config['proxy_senha'],*/
                        'soap_version'   => 'SOAP_1_2',
                        'cache_wsdl'     => 0];
        
        return $aParametros;
    }
    
    /**
     * Adiciona o cabeçalho para autenticação
     */
    private function addHeaders() {
        
        $aHeaders = [new SOAPHeader('ns', 'web-user'    , $this->config['ws_usuario']),
                     new SOAPHeader('ns', 'web-password', $this->config['ws_senha'])];
                    
        $this->Cliente->__setSoapHeaders($aHeaders);
    }
    
    /**
     * Chama o método EnviarEdital do WS com o Xml passado por parâmetro.
     * @param string $sXml
     * @return mixed
     */
    public function enviarEdital($sXml) {
        return $this->getCliente()->EnviarEdital(['editalXml' => file_get_contents($sXml)]);
    }
    
    /**
     * Chama o método ConsultarEditalResultado com o protocolo passado por parâmetro
     * @param string $sProtocolo
     * @return mixed
     */
    public function consultarEditalResultado($sProtocolo) {
        return $this->getCliente()->ConsultarEditalResultado(array('protocolo' => $sProtocolo));
    }
    
    /**
     * Faz o envio do anexo, obrigatório em algumas situações
     * 
     * @param string $sProtocolo Protocolo gerado pelo retorno do método enviarEdital()
     * @param integer $iSequenciaAnexo
     * @param string $sCaminhoAnexo
     * @param string $sNome 
     * @param string $sDescricao
     * @param boolean $isEdital Define se o anexo é um edital
     * @return mixed
     */
    public function enviarAnexo($sProtocolo, $iSequenciaAnexo, $sCaminhoAnexo, $sNome, $sDescricao, $isEdital) {
        $aParametros = ['protocoloProcessamento' => $sProtocolo,
                        'numeroSequencia'        => $iSequenciaAnexo,
                        'anexo'                  => base64_encode(file_get_contents('Arquivo.docx')),
                        'nomeArquivo'            => $sNome,
                        'descricao'              => $sDescricao,
                        'isEdital'               => $isEdital];
    
        return $this->getCliente()->EnviarAnexo($aParametros);
    }
    
    /**
     * Busca todas as unidades de medida do sistema BBMNET
     * 
     * @return mixed
     */
    public function getUnidadesMedida() {
        return $this->getCliente()->ConsultarUnidadesMedida();
    }
}