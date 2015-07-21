<?php
	// ilias initialisierung ohne authentifiezierung
	require_once "./Services/Context/classes/class.ilContext.php";
	ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
	require_once("./Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();

	global $ilDB;

	$host = $ilClientIniFile->readVariable('shadowdb', 'host');
	$user = $ilClientIniFile->readVariable('shadowdb', 'user');
	$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
	$name = $ilClientIniFile->readVariable('shadowdb', 'name');

	$shadowDBCon = new Mysqli($host, $user, $pass, $name) OR die();
	$sql = "SELECT id,usrcrs_row FROM wbd_altdaten WHERE usrcrs_row != -1";

	$res = $shadowDBCon->query($sql);
	while($row = $res->fetch_assoc()){
		$update_hist = "UPDATE hist_usercoursestatus SET gev_id = ".$ilDB->quote($row["id"],"integer")." WHERE row_id = ".$ilDB->quote($row["usrcrs_row"],"integer"). " AND gev_id IS NULL";

		echo $row["id"]." ".$row["usrcrs_row"]." ".$update_hist."<br />";

		$ilDB->manipulate($update_hist);
	}

?>