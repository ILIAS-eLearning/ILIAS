<?php
require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

class ilBuildingBlockPoolPlugin extends ilRepositoryObjectPlugin {

	public function getPluginName() {
		return "BuildingBlockPool";
	}

	protected function beforeActivation()
	{
		parent::beforeActivation();

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
	}
}