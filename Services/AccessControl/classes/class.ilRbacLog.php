<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class ilRbacLog
*  Log changes in Rbac-related settings
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilRbacReview.php 24262 2010-06-15 06:48:14Z nkrzywon $
*
* @ingroup ServicesAccessControl
*/
class ilRbacLog
{
	const EDIT_PERMISSIONS = 1;
	const MOVE_OBJECT = 2;
	const LINK_OBJECT = 3;
	const COPY_OBJECT = 4;
	const CREATE_OBJECT = 5;
	const EDIT_TEMPLATE = 6;
	const EDIT_TEMPLATE_EXISTING = 7;

	static public function gatherFaPa($ref_id, array $role_ids)
	{
		global $rbacreview;

		$result = array();

		// roles
		foreach($role_ids as $role_id)
		{
			if ($role_id != SYSTEM_ROLE_ID)
			{
				$result["ops"][$role_id] = $rbacreview->getRoleOperationsOnObject($role_id, $ref_id);
			}
		}

		// inheritance
		$rolf_data = $rbacreview->getRoleFolderOfObject($ref_id);
		$rolf_id = $rolf_data["child"];
		if($rolf_id && $rolf_id != ROLE_FOLDER_ID)
		{
		   $result["inht"] = $rbacreview->getRolesOfRoleFolder($rolf_id);
		}
		
		return $result;
	}

	static public function diffFaPa(array $old, array $new)
	{
		$result = array();

		// roles
	    foreach($old["ops"] as $role_id => $ops)
		{
			$diff = array_diff($ops, $new["ops"][$role_id]);
			if(sizeof($diff))
			{
				$result["ops"][$role_id]["rmv"] = array_values($diff);
			}
			$diff = array_diff($new["ops"][$role_id], $ops);
			if(sizeof($diff))
			{
				$result["ops"][$role_id]["add"] = array_values($diff);
			}
		}

		if(isset($old["int"]) || isset($new["inht"]))
		{
			if(isset($old["inht"]) && !isset($new["inht"]))
			{
				$result["inht"]["rmv"] = $old["inht"];
			}
			else if(!isset($old["inht"]) && isset($new["inht"]))
			{
				$result["inht"]["add"] = $new["inht"];
			}
			else
			{
				$diff = array_diff($old["inht"], $new["inht"]);
				if(sizeof($diff))
				{
					$result["inht"]["rmv"] = array_values($diff);
				}
				$diff = array_diff($new["inht"], $old["inht"]);
				if(sizeof($diff))
				{
					$result["inht"]["add"] = array_values($diff);
				}
			}
		}

		return $result;
	}

	static public function gatherTemplate($role_folder_ref_id, $role_id)
	{
		global $rbacreview;

		return $rbacreview->getAllOperationsOfRole($role_id, $role_folder_ref_id);
	}

	static public function diffTemplate(array $old, array $new)
	{
		$result = array();
		$types = array_unique(array_merge(array_keys($old), array_keys($new)));
		foreach($types as $type)
		{
			if(!isset($old[$type]))
			{
				$result[$type]["add"] = $new[$type];
			}
			else if(!isset($new[$type]))
			{
				$result[$type]["rmv"] = $old[$type];
			}
			else
			{
				$diff = array_diff($old[$type], $new[$type]);
				if(sizeof($diff))
				{
					$result[$type]["rmv"] = array_values($diff);
				}
				$diff = array_diff($new[$type], $old[$type]);
				if(sizeof($diff))
				{
					$result[$type]["add"] = array_values($diff);
				}
			}
		}
		return $result;
	}

	static public function add($action, $ref_id, array $diff, $source_ref_id = false)
	{
		global $ilUser, $ilDB;

		if(self::isValidAction($action) && sizeof($diff))
	    {
			if($source_ref_id)
			{
				$diff["src"] = $source_ref_id;
			}

			$ilDB->query("INSERT INTO rbac_log (user_id, created, ref_id, action, data)".
				" VALUES (".$ilDB->quote($ilUser->getId(), "integer").",".$ilDB->quote(time(), "integer").
				",".$ilDB->quote($ref_id, "integer").",".$ilDB->quote($action, "integer").
				",".$ilDB->quote(serialize($diff), "text").")");
			return true;
		}
		return false;
	}

	static protected function isValidAction($action)
    {
		if(in_array($action, array(self::EDIT_PERMISSIONS, self::MOVE_OBJECT, self::LINK_OBJECT,
			self::COPY_OBJECT, self::CREATE_OBJECT, self::EDIT_TEMPLATE, self::EDIT_TEMPLATE_EXISTING)))
		{
			return true;
		}
		return false;
	}
}

?>
