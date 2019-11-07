<?php
$baseDir = dirname(dirname(__FILE__));
error_reporting(E_ERROR);
ini_set("soap.wsdl_cache_enabled", 0);
require_once $baseDir.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."sml.config.php";
require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."mail.class.php";
require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."wsMail.php";
?>
