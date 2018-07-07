<?php

include '../src/class_bbmnet.php';

try {
    /* @var $oBbmnet Bbmnet */
    $oBbmnet = new Bbmnet();
    $oBbmnet->setExibeExcetptions(true);
    $oBbmnet->setExibeTrace(true);
    
    $xRetorno = $oBbmnet->enviarEdital('modelo_integracao.xml');
    
    echo '<b>Protocolo Gerado: ' . $xRetorno->EnviarEditalResult . '</b>';
    
    $xRetorno = $oBbmnet->enviarAnexo($xRetorno->EnviarEditalResult, 1, 'Arquivo.docx', 'Modelo Edital', 'Exemplo envio edital', true);
    
    echo '<pre>' . print_r($xRetorno, true) . '</pre>';
} catch(Exception $e) {
    $sMensagem = isset($e->detail->ExceptionDetail->InnerException->Message) ? $e->detail->ExceptionDetail->InnerException->Message : $e->getMessage();
    echo "<b> Ocorreu uma Exceção: </b> " . $sMensagem . '<hr>';
    echo $e . '<hr>';
    echo '<pre>' . print_r($e, true) . '</pre>';
}