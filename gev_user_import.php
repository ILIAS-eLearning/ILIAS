<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_STRICT);
header("Content-Type: text/plain, charset=utf-8");



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


function get_gev_import() {
	global $ilClientIniFile, $ilDB;

	$host = $ilClientIniFile->readVariable('shadowdb', 'host');
	$user = $ilClientIniFile->readVariable('shadowdb', 'user');
	$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
	$name = $ilClientIniFile->readVariable('shadowdb', 'name');

	$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
	mysql_select_db($name, $mysql);

	include("./Services/IVImport/classes/class.gevUserImport.php");
	$import = gevUserImport::getInstance($mysql, $ilDB);
	return $import;
}

import_ilias();
$import = get_gev_import();

$action = $_GET['action'];
switch ($action) {

	case 'register':
		$stelle = $_GET['stellennummer'];
		$email = $_GET['email'];

		$error = $import->register($stelle, $email);
		if ($error) {
			die($error);
		}

		header('Location: /');
		break;

	case 'activate':
		$token = $_GET['token'];

		$error = $import->activate($token);

		if ($error) {
			die($error);
		}

		break;

	default:
		break;
}

?>
