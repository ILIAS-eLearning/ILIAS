<?php
chdir("./../../../");
$ini = new ilIniFile("ilias.ini.php");
$ini->read();

$http = $ini->readVariable("server", "http_path");
$http = preg_replace("/^(https:\/\/)|(http:\/\/)+/", "", $http);

$_SERVER['HTTP_HOST'] = $http;
$_SERVER['REQUEST_URI'] = "";

ilInitialisation::initILIAS();

throw new Exception("This is your error message");
