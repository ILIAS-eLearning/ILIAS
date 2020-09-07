<?php
chdir("./../../../");
require_once("Services/Init/classes/class.ilIniFile.php");
$ini = new ilIniFile("ilias.ini.php");
$ini->read();

$http = $ini->readVariable("server", "http_path");
$http = preg_replace("/^(https:\/\/)|(http:\/\/)+/", "", $http);

$_SERVER['HTTP_HOST'] = $http;
$_SERVER['REQUEST_URI'] = "";

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

global $DIC;

$ilErr = $DIC['ilErr'];

$ilErr->raiseError("This is your error message", $ilErr->FATAL);
