<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$baseDir = dirname(__FILE__);



/******************************************************************************/
/*                                                                            */
/*                         MAIL IN USCITA                                     */
/*                                                                            */
/******************************************************************************/
require_once "config/sml.config.php";
require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."mail.class.php";


function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

$uuid = generateRandomString(32);

$type="IN";
if($type=="OUT"){
    $oggetto = "Richiesta Integrazioni pratica XXXX";
    $message =<<<EOT
Si comunica che in data 22/08/2019 è pervenuta la pratica XXXX con protocollo YYYYY mancante dei seguenti allegati:

- Documento di Indentità di AAA BBB
- Elaborati Grafici (insufficienti)
            
La pratica deve essere integrata al più presto.
            
Cordialmente
    Geom ZZZZ RRRR
EOT;
    $to = Array("carbone.marco@pec.it","amministrazione@pec.gisweb.it");
    $cc = Array("carboneXXX.marco@pec.it");
    
    $res = gwMail::send($to, $oggetto, $message, Array(), 0, $cc);
    print_r($res);
}
else{        
/******************************************************************************/
/*                                                                            */
/*                         MAIL IN INGRESSO                                   */
/*                                                                            */
/******************************************************************************/
    $uuid = "UQBXZlSqfAHPj8Y2hpk59LvitNJ3saFM";
    $pec = "carboneXXX.marco@pec.it";
    $res = gwMail::getConsegna($uuid,$pec);
    //print_r($res);
}
?>