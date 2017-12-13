<?php
require_once "url_tools.php";
define (
    "URL_DE_TESTE_PARA_CONSUMO",
    "https://apod.nasa.gov/apod/ap171210.html"
//"http://arturmarques.com/"
);
define ("APOD_PREFIX", "https://apod.nasa.gov/apod/");
function apodUrlRelativeToAbsolute(
    $pUrlRelative
){
    $urlAbs = APOD_PREFIX.$pUrlRelative;
    return $urlAbs;
}//apodUrlRelativeToAbsolute
function downloaderInseguro(
    $pUrl
){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//inseguro
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    $resultadoFalseSeFracassoOuSeqDeBytesSeSucesso =
        curl_exec($ch);
    return $resultadoFalseSeFracassoOuSeqDeBytesSeSucesso;
}//downloaderInseguro
function gravadorAutomaticoDeDownloadParaFicheiro(
    $pBytesParaGravar,
    $pNomeDoFicheiro = null
){
    //exemplo de nome para ficheiro
    //2017-12-11-12-38-00.BIN
    $nomeParaFicheiro =
        ($pNomeDoFicheiro===null) ?
            date("Y-m-d-G-i-s").".BIN"
            :
            $pNomeDoFicheiro;
    $ret = file_put_contents(
        $nomeParaFicheiro, // can NOT be empty
        $pBytesParaGravar
    );
    return $ret ? $nomeParaFicheiro : false ;
}//gravadorAutomaticoDeDownloadParaFicheiro
function urlsPresentesNoURL(
    $pUrl //e.g. "http://arturmarques.com/"
){
    $htmlSourceCode = downloaderInseguro($pUrl);
    $urlsDescobertosNoHTML =
        urlsPresentesNoHTML($htmlSourceCode);
    return $urlsDescobertosNoHTML;
}//urlsPresentesNoURL
define ("MARCADOR_DE_HREFS", "<a href=\"");
define ("KEY_ABS_URL", "ABS");
define ("KEY_REL_URL", "REL");
function organizadorFiltrador(
    $pAUrls, //coleção de URLs, relativos, absolutos, de imagens, de outras coisas
    $pExtensoesAceites = null //null simbolizando q se aceita tudo ; filtrar será com arrays como [".jpg", ".png"]
){
    $ret = [
        KEY_ABS_URL => [], //col de URLs absolutos encontrados
        KEY_REL_URL => [] //col de URLs relativos encontrados
    ];
    $bCautela = is_array($pAUrls) && count($pAUrls)>0;
    if ($bCautela){
        foreach ($pAUrls as $url){
            $bUrlAbsoluto = urlAbsoluto($url);
            $bSatisfazAlgumaDasExtensoesAceites =
                urlTerminaEm(
                    $url,
                    $pExtensoesAceites
                );
            //se o URL é absoluto e satisfaz a filtragem, vai para a sub-col abs
            if ($bUrlAbsoluto && $bSatisfazAlgumaDasExtensoesAceites){
                $ret[KEY_ABS_URL][] = $url;
            }
            //se o URL é relativo e satisfaz a filtragem, vai para sub-col rel
            if (!$bUrlAbsoluto && $bSatisfazAlgumaDasExtensoesAceites){
                $ret[KEY_REL_URL][] = $url;
            }
        }//foreach
    }//if
    return $ret;
}//filtradorDeUrls
function urlsPresentesNoHTML(
    $pSourceCodeHTML
){
    $urls = [];
    /*
     * exemplo de explode
     * $s = "bla\tble\tbli"
     * explode ("\t", $s) ----> ["bla", "ble", "bli"]
     *
     */
    $partesExigindoMaisParsingParaIsolarUrls =
        explode(MARCADOR_DE_HREFS, $pSourceCodeHTML);
    $parteNumero = 0;
    foreach (
        $partesExigindoMaisParsingParaIsolarUrls
        as
        $parte
    ){
        //rejeitar a primeira parte, porque é "lixo"
        $parteMereceAtencao = $parteNumero>0;
        if ($parteMereceAtencao){
            /*
            cada parte tem o URL que interessa desde
            a sua posição 0 até à posição em que ocorra
            a primeira aspa (que simboliza o fim do valor
            do valor href
            exemplo:
            $parte <--- "<a href=\"http://arturmarques.com/\">..."
            */
            /*
             * exemplos
             * strpos("ABC", "BC") ---> 1
             * strpos("ABC", "bc") ---> false
             * stripos("ABC", "bc") ---> 1 (procura case INsensitive)
             * stripos("ABCC", "c") ---> 2
             * strripos("ABCC", "c") ---> 3 (r rightmost)
             * strripos("ABCCC", "c") ---> 4
             */
            $posicaoDaAspaDeEncerramento =
                stripos($parte, "\"");
            $aspaExiste =
                $posicaoDaAspaDeEncerramento!==false;
            /*
             * substr ($frase, $posDePartida, $quantidade)
             */
            if ($aspaExiste){
                $url = substr(
                    $parte,
                    0,
                    $posicaoDaAspaDeEncerramento
                );
                $urls[] = $url;
            }//if
        }//if
        $parteNumero++;
    }//foreach
    return $urls;
}//urlsPresentesNoHTML
function apodGetAbsoluteUrlForIOTDAtHtmlUrl(
    $pApodHtmlUrl = URL_DE_TESTE_PARA_CONSUMO
){
    $ret = false;
    $todosOsUrl =
        urlsPresentesNoURL($pApodHtmlUrl);
    $filtrosAceitacao =[".jpg", ".png"];
    $urlsAposOrganizacaoEFiltragem =
        organizadorFiltrador($todosOsUrl, $filtrosAceitacao);
    $urlsDoDia = $urlsAposOrganizacaoEFiltragem[KEY_REL_URL];
    $bCautela = count($urlsDoDia)>=1;
    if ($bCautela){
        $ret = apodUrlRelativeToAbsolute($urlsDoDia[0]);
    }
    return $ret;
}//apodGetAbsoluteUrlForIOTDAtHtmlUrl
//git clone https://www.github.com/amsm/aca171213.git
/*
echo apodGetAbsoluteUrlForIOTDAtHtmlUrl
    (URL_DE_TESTE_PARA_CONSUMO);
*/
function apodGetAbsoluteUrlForIOTD(
    $pY,
    $pM,
    $pD
){
    $htmlUrl = apodHtmlUrlForDate ($pY, $pM, $pD);
    return apodGetAbsoluteUrlForIOTDAtHtmlUrl($htmlUrl);
}//apodGetAbsoluteUrlForIOTD
//https://apod.nasa.gov/apod/ap171210.html
function apodHtmlUrlForDate (
    $pY,
    $pM,
    $pD
){
    $tempoCorrespondenteData =
        mktime(null, null, null, $pM, $pD, $pY);
    $yymmdd = date("ymd", $tempoCorrespondenteData);
    $url = APOD_PREFIX."ap$yymmdd.html";
    return $url;
}//apodHtmlUrlForDate
function downloadApodDoDia(
    $pY, $pM, $pD
){
    $urlDaImagem = apodGetAbsoluteUrlForIOTD($pY, $pM, $pD);
    $nomePerfeitoParaImagem = substr(
        $urlDaImagem,
        strripos($urlDaImagem, "/")+1
    );
    $ficheiroGravado =
        gravadorAutomaticoDeDownloadParaFicheiro(
            $bytesDaImagem = downloaderInseguro($urlDaImagem),
            $nomePerfeitoParaImagem
        );
    return $ficheiroGravado;
}//downloadApodDoDia
//echo apodGetAbsoluteUrlForIOTD(2016, 12, 25);
echo downloadApodDoDia(2017, 12, 13);