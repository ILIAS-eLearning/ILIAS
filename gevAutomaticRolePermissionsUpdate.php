<?php
/**
* Aktualisiert an bestehenden Kursen bestehende Rollen mit bestehenden Rechten.
* Dafür muss eine Datei mit folgenden anfprderungen angelegt werden.
*
* Zeile 1: SELECT Statement um die ref_id der zu ändernden Kurse aus zu lesen
* Zeilen 2-n: Rollenname;Rechtname
*			Es können mehrere Rechte einer Rolle zugewiesen werden. Dafür müssen.
*			die Rechtenamen mit einer Pipe( | ) getrennt werden.
*
* Damit die Dateien von anderen unterschieden werden können, MUSS die Dateiendung "rp" verwendet werden.
*
* Beispiel:
* Dateiname: test.rr
* Inhalt: 
*		SELECT ref_id FROM object_reference WHERE obj_id = 2010
*		User;change_trainer|load_csn_list
*		Guest;change_trainer|load_csn_list
*
* WICHTIG!!!!!
* Definieren Sie in der Variable $folder_path wo sich die Dateien für die Zuordung befinden
*
*/
require_once("./Services/Init/classes/class.ilInitialisation.php");
require_once("./Services/AccessControl/classes/class.ilRbacReview.php");
ilInitialisation::initILIAS();

global $ilDB;

$folder_path = "role_permissions_association";
$row_regex = "/^(SELECT)/";
$role_ids = array();
$count_files = 0;

//Ordner auslesen
$files = scandir($folder_path);
//Wenn im Ordner Dateien waren
if($files) {
	//jede datei einzeln verarbeiten
	foreach ($files as $value) {
		$path_info = pathinfo($folder_path."/".$value);
		if($path_info["extension"] != "rp") {
			continue;
		}

		$count_files++;
		if(file_exists($folder_path."/".$value)) {
			$fh = fopen($folder_path."/".$value,"r");
			$counter = 0;
			$query = "";
			$role_permissions = array();
			
			echo "##########################<br/>";
			echo "Datei: ".$folder_path."/".$value." wird ausgelesen!<br/>";

			while($row = fgets($fh)) {
				$row = str_replace("\n", "", $row);
				if($counter == 0) {
					if(preg_match($row_regex,$row)) {
						$query = $row;
						$counter++;
					}
					continue;
				}
				$counter++;
				$rp = explode(";", $row);

				if($rp[1] == "") {
					echo "Kein Rechte f&uuml;r die Rolle <strong>'".$rp[0]."'</strong> angegeben. Zeile: ".$counter."<br/>";
					continue;
				}
				$role_permissions[$rp[0]] = $rp[1];
			}
			if($query == "") {
				echo "Kein SQL Statement vorhanden<br/><br/>";
				continue;
			}

			if(empty($role_permissions)) {
				echo "Keine Rollen definiert.<br/><br/>";
				continue;
			}

			$res = $ilDB->query($query);
			while($row = $ilDB->fetchAssoc($res)) {
				$ref_id = $row["ref_id"];

				if(!userCanEditPermissions($ref_id)) {
					echo "Benutzer hat nicht gen&uuml;gend Rechte um am Kurs ".$ref_id." Rechte zu &auml;ndern!<br/>";
					continue;
				}

				foreach ($role_permissions as $role => $permissions) {
					if(!array_key_exists($role, $role_ids)) {
						$role_id = getRoleIdByName($role);
						if($role_id === null) {
							continue;
						}
						$role_ids[$role] = $role_id;
					}

					updatePermissions($role_ids[$role], $ref_id, $permissions);
				}
			}
			echo "<br/>";
		}
	}
	if($count_files > 0) {
		echo "##########################<br/>";
		echo "Alle neuen Rechte wurde verteilt!<br/>";
		echo "##########################<br/>";
	} else {
		echo "##########################<br/>";
		echo "Keine Dateien gefunden!<br/>";
		echo "##########################<br/>";
	}
	
} else {
	echo "##########################<br/>";
	echo "Keine Dateien gefunden!<br/>";
	echo "##########################<br/>";
}

echo "##########################<br/>";
echo "Script Ende!<br/>";
echo "##########################<br/>";


function getRoleIdByName($a_role_name) {
	global $ilDB;
	$res = $ilDB->query( "SELECT od.obj_id"
							."  FROM object_data od"
							." WHERE title = ".$ilDB->quote($a_role_name, "text")
							."   AND type = 'role'"
							);

	if ($rec = $ilDB->fetchAssoc($res)) {
		return $rec["obj_id"];
	}
	return null;
}

function userCanEditPermissions($ref_id) {
	global $ilUser,$rbacsystem;
	return $rbacsystem->checkAccessOfUser($ilUser->getId(), "edit_permission", $ref_id);
}

function updatePermissions($role_id,$ref_id,$permissions) {
	global $rbacreview,$rbacadmin;

	//alte rechte laden
	$cur_ops = $rbacreview->getRoleOperationsOnObject($role_id,$ref_id);
	//ids von neuen rechten suchen
	echo "Folgende Rechte werden f&uuml;r <strong>'".$role."'</strong> hinzugef&uuml;gt.";
	echo "<ul>";
	foreach (explode("|",$permissions) as $value) {
		echo "<li>".$value."</li>";
	}
	echo "</ul>";
	$grant_ops = ilRbacReview::_getOperationIdsByName(explode("|",$permissions));
	//alt und neu zusammen fügen
	$new_ops = array_unique(array_merge($grant_ops, $cur_ops));
	//alte löschen
	$rbacadmin->revokePermission($ref_id, $role_id);
	//neue ran basteln
	$rbacadmin->grantPermission($role_id, $new_ops, $ref_id);
}