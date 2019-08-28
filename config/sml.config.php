<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
define('MAILPEC',1);
define('MAILHOSTOUT', "smtps.pec.aruba.it");
define('MAILHOSTIN', "imaps.pec.aruba.it");
define("MAILUSER", "amministrazione@pec.gisweb.it");
define("MAILPWDIN", "KYSTZPCFDD");
define("MAILPWDOUT", "KYSTZPCFDD");
define("MAILFROM", "amministrazione@pec.gisweb.it");
define("MAILALIAS","Gis&Web S.A.S.");
define("MAILPORTOUT", "465");
define("MAILPORTIN", "993");
define("MAILTLSIN", 'notls');
define("MAILSSLIN", 'ssl');
define("MAILSECURE", 'ssl');
define('MAILAUTH',true);
 */
$actualDir = dirname(__FILE__);
define('MAILPEC',1);
define('MAILHOSTOUT', "smtps.pec.aruba.it");
define('MAILHOSTIN', "imaps.pec.aruba.it");
define("MAILUSER", "carbone.marco@pec.it");
define("MAILPWDIN", "4lf40m3g4");
define("MAILPWDOUT", "4lf40m3g4");
define("MAILFROM", "carbone.marco@pec.it");
define("MAILALIAS","Marco Carbone");
define("MAILPORTOUT", "465");
define("MAILPORTIN", "993");
define("MAILTLSIN", 'notls');
define("MAILSSLIN", 'ssl');
define("MAILSECURE", 'ssl');
define('MAILAUTH',true);
if(file_exists($actualDir.DIRECTORY_SEPARATOR."sml.config.local.php")){
    require_once $actualDir.DIRECTORY_SEPARATOR."sml.config.local.php";
}
else{
    define('MAILDSN',"pgsql:dbname=gw_sml;user=gwAdmin;password=!{!dpQ3!Hg7kdCA9;host=127.0.0.1;port=5434");
}
$project = "sml";
$url = "http://webservice.gisweb.it/wsmail/$project.wsMail.php?wsdl";
define('SERVICE_URL',$url);
?>