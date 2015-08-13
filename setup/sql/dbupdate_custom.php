<#1>
<?php

$res = $ilDB->query("SELECT a.id, a.tpl_id, od.obj_id , od.title FROM ".
			"(didactic_tpl_a a JOIN ".
			"(didactic_tpl_alr alr JOIN ".
			"object_data od ".
			"ON (alr.role_template_id = od.obj_id)) ".
			"ON ( a.id = alr.action_id)) ".
			"WHERE a.type_id = " . $ilDB->quote(2,'integer'));

$names = array();
$templates = array();

while($row = $ilDB->fetchAssoc($res))
{
	$names[$row["tpl_id"]][$row["id"]] = array(
					"action_id" => $row["id"],
					"role_template_id" => $row["obj_id"],
					"role_title" => $row["title"]);

	$templates[$row["tpl_id"]] = $row["tpl_id"];
}

$res = $ilDB->query("SELECT * FROM didactic_tpl_objs");

while($row = $ilDB->fetchAssoc($res))
{
	if(in_array($row["tpl_id"],$templates))
	{
		$roles = array();
		$rol_res = $ilDB->query("SELECT rol_id FROM rbac_fa ".
			"WHERE parent = ".$ilDB->quote($row["ref_id"],'integer'). " AND assign = ".$ilDB->quote('y','text'));

		while($rol_row = $ilDB->fetchObject($rol_res))
		{
			$roles[] = $rol_row->rol_id;
		}

		foreach($names[$row["tpl_id"]] as $name)
		{
			$concat = $ilDB->concat(array(
					array("title", "text"),
					array($ilDB->quote("_".$row["ref_id"], "text"), "text")
				), false);

			$ilDB->manipulate("UPDATE object_data".
				" SET title = ".$concat .
				" WHERE ".$ilDB->in("obj_id",$roles, "", "integer").
				" AND title = " . $ilDB->quote($name['role_title']));
		}
	}
}
?>