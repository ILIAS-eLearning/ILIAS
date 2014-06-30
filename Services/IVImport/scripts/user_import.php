<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
chdir("../../../");  // I was told that this is OK.
ini_set('display_errors', 1);
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

$import = gevUserImport::getInstance($mysql, $ilDB);

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

		header('Location: /');

		break;

	default:
		break;
}

?>
