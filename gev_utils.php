<?php


function get_gev_import() {
	global $ilClientIniFile, $ilDB;

	$host = $ilClientIniFile->readVariable('shadowdb', 'host');
	$user = $ilClientIniFile->readVariable('shadowdb', 'user');
	$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
	$name = $ilClientIniFile->readVariable('shadowdb', 'name');

	$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
	mysql_select_db($name, $mysql);
	mysql_set_charset('utf8', $mysql);

	include("./Services/IVImport/classes/class.gevUserImport.php");
	$import = gevUserImport::getInstance($mysql, $ilDB);
	return $import;
}

?>
