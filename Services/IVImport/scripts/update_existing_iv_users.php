<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
chdir("../../../");
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
error_reporting(E_ALL ^ E_STRICT);
require_once("./include/inc.header.php");
require_once("./Services/IVImport/classes/class.gevUserImport.php");

header("Content-Type: text/plain, charset=utf-8");

global $ilClientIniFile;

$host = $ilClientIniFile->readVariable('shadowdb', 'host');
$user = $ilClientIniFile->readVariable('shadowdb', 'user');
$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
$name = $ilClientIniFile->readVariable('shadowdb', 'name');

$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
mysql_select_db($name, $mysql);
mysql_set_charset('utf8', $mysql);

$import = gevUserImport::getInstance($mysql, $ilDB);
$import->update_imported_shadow_users();

?>
