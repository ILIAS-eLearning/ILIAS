<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
chdir("../../../");
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
error_reporting(E_ALL ^ E_STRICT);

function import_ilias() {
    // ILIAS core requires an authenticated user to use its API, unless the
    // called script name is index.php (see ilInitialisation::authenticate()).
    // When our script is being executed, we don't have a user and thus
    // cannot authenticate them - which means we need to fiddle with the
    // environment variables to work around this behaviour.
    $php_self = $_SERVER['PHP_SELF'];
    $_SERVER['PHP_SELF'] = str_replace(basename(__file__), 'index.php', $php_self);
    include("./include/inc.header.php");
    $_SERVER['PHP_SELF'] = $php_self;
}

import_ilias();

require_once("./Services/IVImport/classes/class.gevUserImport.php");

header("Content-Type: text/plain, charset=utf-8");

global $ilClientIniFile;

if ($_GET['pw'] != $ilClientIniFile->readVariable('shadowdb', 'web_access_password')) {
    die("Invalid password.");
}

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
