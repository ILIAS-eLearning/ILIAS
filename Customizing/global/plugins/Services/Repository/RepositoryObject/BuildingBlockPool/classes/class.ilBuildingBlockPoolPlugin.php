<?php
require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

class ilBuildingBlockPoolPlugin extends ilRepositoryObjectPlugin {

	public function getPluginName() {
		return "BuildingBlockPool";
	}

	protected function beforeActivation()
	{
		parent::beforeActivation();
		global $ilDB;
		// before activating, we ensure, that the type exists in the ILIAS
		// object database and that all permissions exist
		$type = $this->getId();
		
		if (substr($type, 0, 1) != "x")
		{
			throw new ilPluginException("Object plugin type must start with an x. Current type is ".$type.".");
		}
		
		// check whether type exists in object data, if not, create the type
		$set = $ilDB->query("SELECT * FROM object_data ".
			" WHERE type = ".$ilDB->quote("typ", "text").
			" AND title = ".$ilDB->quote($type, "text")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$t_id = $rec["obj_id"];
		}
		else
		{
			$t_id = $ilDB->nextId("object_data");
			$ilDB->manipulate("INSERT INTO object_data ".
				"(obj_id, type, title, description, owner, create_date, last_update) VALUES (".
				$ilDB->quote($t_id, "integer").",".
				$ilDB->quote("typ", "text").",".
				$ilDB->quote($type, "text").",".
				$ilDB->quote("Plugin ".$this->getPluginName(), "text").",".
				$ilDB->quote(-1, "integer").",".
				$ilDB->quote(ilUtil::now(), "timestamp").",".
				$ilDB->quote(ilUtil::now(), "timestamp").
				")");
		}

		// add rbac operations for plugin
		// 58: copy	
		$ops = array(58);
		
		foreach ($ops as $op)
		{
			// check whether type exists in object data, if not, create the type
			$set = $ilDB->query("SELECT * FROM rbac_ta ".
				" WHERE typ_id = ".$ilDB->quote($t_id, "integer").
				" AND ops_id = ".$ilDB->quote($op, "integer")
				);
			if (!$ilDB->fetchAssoc($set))
			{
				$ilDB->manipulate("INSERT INTO rbac_ta ".
					"(typ_id, ops_id) VALUES (".
					$ilDB->quote($t_id, "integer").",".
					$ilDB->quote($op, "integer").
					")");
			}
		}

		return true;
	}
}