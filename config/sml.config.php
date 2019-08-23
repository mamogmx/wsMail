<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
define('MAILDSN',"pgsql:dbname=gw_alghero;user=postgres;password=postgres;host=127.0.0.1;port=5434");
?>