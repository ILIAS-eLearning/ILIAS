<?php

die("Go away...");

header("Content-Type: text/plain");

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

define("DRY_RUN", true);

// Get org unit for EVG as we only shorten the names there.

$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
					."  FROM object_data od "
					."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
					." WHERE ".$ilDB->in("import_id", array("evg"), false, "text")
					."   AND oref.deleted IS NULL"
					."   AND od.type = 'orgu'"
					);

if ($rec = $ilDB->fetchAssoc($res)) {
	$evg_ref_id = $rec["ref_id"];
}
else {
	die("Could not find orgu with import id evg.");
}

// We need to do this on all org units below evg:
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

$evg_children = gevOrgUnitUtils::getAllChildren(array($evg_ref_id));

foreach ($evg_children as $child) {
	$orgu_utils = gevOrgUnitUtils::getInstance($child["ref_id"]);
	$obj_id = $child["obj_id"];
	
	$cur_title = $orgu_utils->getTitle();
	
	$matches = array();
	if (preg_match("/^Bereichsdirektion (.*)$/", $cur_title, $matches)) {
		$new_title = "BD ".$matches[1];
	}
	else if (preg_match("/^Organisationsdirektion (.*)$/", $cur_title, $matches)) {
		$new_title = "OD ".$matches[1];
	}
	else if (preg_match("/^Filialdirektion (.*)$/", $cur_title, $matches)) {
		$new_title = "FD ".$matches[1];
	}
	else if (preg_match("/^Unternehmeragentur (.*)$/", $cur_title, $matches)) {
		$new_title = "UA ".$matches[1];
	}
	else {
		echo "No replacement for: $title\n";
		continue;
	}
	
	echo "Replacing '$cur_title' with '$new_title'...";
	
	if (DRY_RUN) {
		continue;
	}
	
	// Updating original title.
	$ilDB->manipulate("UPDATE obj_data \n"
					 ."   SET title = ".$ilDB->quote($new_title, "text")
					 -" WHERE obj_id = ".$ilDB->quote($obj_id, "integer")
					 );
	
	$ilDB->manipulate("UPDATE hist_user \n"
					 ."   SET org_unit = ".$ilDB->quote($new_title, "text")
					 ." WHERE org_unit = ".$ilDB->quote($cur_title, "text")
					 );
	
	$ilDB->manipulate("UPDATE hist_user \n"
					 ."   SET org_unit_above1 = ".$ilDB->quote($new_title, "text")
					 ." WHERE org_unit_above1 = ".$ilDB->quote($cur_title, "text")
					 );
	
	$ilDB->manipulate("UPDATE hist_user \n"
					 ."   SET org_unit_above2 = ".$ilDB->quote($new_title, "text")
					 ." WHERE org_unit_above2 = ".$ilDB->quote($cur_title, "text")
					 );
	
	$ilDB->manipulate("UPDATE hist_tep \n"
					 ."   SET orgu_title = ".$ilDB->quote($new_title, "text")
					 ." WHERE orgu_title = ".$ilDB->quote($cur_title, "text")
					 );
	
	$ilDB->manipulate("UPDATE hist_usercoursestatus \n"
					 ."   SET org_unit = ".$ilDB->quote($new_title, "text")
					 ." WHERE org_unit = ".$ilDB->quote($cur_title, "text")
					 );
}

?>
