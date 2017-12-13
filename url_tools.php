<?php
//url_tools.php
/**
 * Created by PhpStorm.
 * User: research
 * Date: 2017-12-13
 * Time: 11:18
 */
define (
    "PREFIXOS_DE_URLS_ABSOLUTOS",
    ["http://", "https://", "ftp://", "ftps://", "telnet://", "mailto://"]
);
define ("ACEITAR_TUDO", null);
/*
 * (c) Pessoa 1, 2017
 * recebe uma String que deverá representar um URL
 * retorna true se a String recebida for um "url absoluto", false c.c.
 */
function urlAbsoluto(
    $pUrl
){
    $ret = false;
    $bCautela = is_string($pUrl) && strlen($pUrl)>0;
    if ($bCautela){
        foreach (PREFIXOS_DE_URLS_ABSOLUTOS as $prefixo){
            $bUrlComecaPeloPrefixoEmAnalise =
                stripos($pUrl, $prefixo)===0;
            if ($bUrlComecaPeloPrefixoEmAnalise)
                return true;
        }//foreach
    }//if
    return $ret;
}//urlAbsoluto
function urlTerminaEm(
    $pUrl,
    $pTerminacoesAceites = ACEITAR_TUDO
){
    $bCautela =
        is_array($pTerminacoesAceites) && count($pTerminacoesAceites)>0;
    if ($bCautela){
        foreach ($pTerminacoesAceites as $terminacao){
            $iPosUltimaOcorrenciaDaTerminacaoNoUrl =
                stripos($pUrl, $terminacao);
            $bTerminacaoFoiEncontrada =
                $iPosUltimaOcorrenciaDaTerminacaoNoUrl!==false;
            if ($bTerminacaoFoiEncontrada){
                $tamanhoDoUrl = strlen($pUrl);
                $iPosEmQueTerminacaoTemQueEstarParaSerFinalizadoraDoUrl =
                    $tamanhoDoUrl - strlen($terminacao);
                $bTerminacaoEstaMesmoNoFim =
                    $iPosUltimaOcorrenciaDaTerminacaoNoUrl
                    ===
                    $iPosEmQueTerminacaoTemQueEstarParaSerFinalizadoraDoUrl;
                if ($bTerminacaoEstaMesmoNoFim){
                    return true; //uma das terminações foi satisfeita, logo o url satisfaz uma das possibilidades
                }
            }//if
        }//foreach
    }//if
    else{
        return true; //nenhum filtro foi imposto, qq URL satisfaz
    }//else
    return false; //nenhuma das terminações aceites terminou o URL
}//urlTerminaEm
function filtrarUrls(
    $pAUrls,
    $pFiltros = ACEITAR_TUDO
){
    $ret = [];
    foreach($pAUrls as $url){
        if(urlTerminaEm($url, $pFiltros)) $ret[]=$url;
    }//foreach
    return $ret;
}//filtrarUrls