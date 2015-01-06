<?php

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all repository object plugin classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRepository
*/
abstract class ilRepositoryObjectPlugin extends ilPlugin
{
	/**
	* Get Component Type
	*
	* @return        string        Component Type
	*/
	final function getComponentType()
	{
			return IL_COMP_SERVICE;
	}
	
	/**
	* Get Component Name.
	*
	* @return        string        Component Name
	*/
	final function getComponentName()
	{
			return "Repository";
	}

	/**
	* Get Slot Name.
	*
	* @return        string        Slot Name
	*/
	final function getSlot()
	{
			return "RepositoryObject";
	}

	/**
	* Get Slot ID.
	*
	* @return        string        Slot Id
	*/
	final function getSlotId()
	{
			return "robj";
	}

	/**
	* Object initialization done by slot.
	*/
	protected final function slotInit()
	{
			// nothing to do here
	}
	
	/**
	* Get Icon
	*/
	static function _getIcon($a_type, $a_size)
	{
		switch($a_size)
		{
			case "small": $suff = ""; break;
			case "tiny": $suff = "_s"; break;
			default: $suff = "_b"; break;
		}
		return ilPlugin::_getImagePath(IL_COMP_SERVICE, "Repository", "robj",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj",$a_type),
			"icon_".$a_type.$suff.".svg");
	}
	
	/**
	* Get class name
	*/
	function _getName($a_id)
	{
		$name = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj",$a_id);
		if ($name != "")
		{
			return $name;
		}
	}
	
	/**
	* Before activation processing
	*/
	protected function beforeActivation()
	{
		global $lng, $ilDB;
		
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

		// add rbac operations
		// 1: edit_permissions, 2: visible, 3: read, 4:write, 6:delete
		$ops = array(1, 2, 3, 4, 6);
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
		
		// now add creation permission, if not existing
		$set = $ilDB->query("SELECT * FROM rbac_operations ".
			" WHERE class = ".$ilDB->quote("create", "text").
			" AND operation = ".$ilDB->quote("create_".$type, "text")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$create_ops_id = $rec["ops_id"];
		}
		else
		{
			$create_ops_id = $ilDB->nextId(rbac_operations);
			$ilDB->manipulate("INSERT INTO rbac_operations ".
				"(ops_id, operation, description, class) VALUES (".
				$ilDB->quote($create_ops_id, "integer").",".
				$ilDB->quote("create_".$type, "text").",".
				$ilDB->quote("create ".$type, "text").",".
				$ilDB->quote("create", "text").
				")");
		}
		
		// assign creation operation to root, cat, crs, grp and fold
		$par_types = array("root", "cat", "crs", "grp", "fold");
		foreach ($par_types as $par_type)
		{
			$set = $ilDB->query("SELECT obj_id FROM object_data ".
				" WHERE type = ".$ilDB->quote("typ", "text").
				" AND title = ".$ilDB->quote($par_type, "text")
				);
			if ($rec = $ilDB->fetchAssoc($set))
			{
				if ($rec["obj_id"] > 0)
				{
					$set = $ilDB->query("SELECT * FROM rbac_ta ".
						" WHERE typ_id = ".$ilDB->quote($rec["obj_id"], "integer").
						" AND ops_id = ".$ilDB->quote($create_ops_id, "integer")
						);
					if (!$ilDB->fetchAssoc($set))
					{
						$ilDB->manipulate("INSERT INTO rbac_ta ".
							"(typ_id, ops_id) VALUES (".
							$ilDB->quote($rec["obj_id"], "integer").",".
							$ilDB->quote($create_ops_id, "integer").
							")");
					}
				}
			}
		}
		
		return true;
	}

}
?>
