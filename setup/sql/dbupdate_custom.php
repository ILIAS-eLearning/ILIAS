<#1>
<?php
	// register new object type 'logs' for Logging administration
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "typ", "logs", "Logging Administration", -1, ilUtil::now(), ilUtil::now()));
	$typ_id = $id;

	// create object data entry
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "logs", "__LoggingSettings", "Logging Administration", -1, ilUtil::now(), ilUtil::now()));

	// create object reference entry
	$ref_id = $ilDB->nextId('object_reference');
	$res = $ilDB->manipulateF("INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($ref_id, $id));

	// put in tree
	$tree = new ilTree(ROOT_FOLDER_ID);
	$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

	// add rbac operations
	// 1: edit_permissions, 2: visible, 3: read, 4:write
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 1));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 2));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 3));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 4));


?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>