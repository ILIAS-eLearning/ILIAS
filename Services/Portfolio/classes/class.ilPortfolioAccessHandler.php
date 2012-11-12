<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Portfolio/classes/class.ilObjPortfolio.php";
include_once "Modules/Group/classes/class.ilGroupParticipants.php";
include_once "Modules/Course/classes/class.ilCourseParticipants.php";
include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";

/**
 * Access handler for portfolio
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
 * 
 * @ingroup ServicesPortfolio
 */
class ilPortfolioAccessHandler
{
	public function __construct()
	{
		global $lng;
		$lng->loadLanguageModule("wsp");
	}

	/**
	 * check access for an object
	 *
	 * @param	string		$a_permission
	 * @param	string		$a_cmd
	 * @param	int			$a_node_id
	 * @param	string		$a_type (optional)
	 * @return	bool
	 */
	public function checkAccess($a_permission, $a_cmd, $a_node_id, $a_type = "")
	{
		global $ilUser;

		return $this->checkAccessOfUser($ilUser->getId(),$a_permission, $a_cmd, $a_node_id, $a_type);
	}

	/**
	 * check access for an object
	 *
	 * @param	integer		$a_user_id
	 * @param	string		$a_permission
	 * @param	string		$a_cmd
	 * @param	int			$a_node_id
	 * @param	string		$a_type (optional)
	 * @return	bool
	 */
	public function checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $a_node_id, $a_type = "")
	{
		global $rbacreview, $ilUser;

		// :TODO: create permission for parent node with type ?!
		
		$pf = new ilObjPortfolio($a_node_id, false);
		if(!$pf->getId())
		{
			return false;
		}
		
		// portfolio owner has all rights
		if($pf->getOwner() == $a_user_id)
		{
			return true;
		}

		// other users can only read
		if($a_permission == "read" || $a_permission == "visible")
		{
			// get all objects with explicit permission
			$objects = $this->getPermissions($a_node_id);
			if($objects)
			{
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
				
				// check if given user is member of object or has role
				foreach($objects as $obj_id)
				{
					switch($obj_id)
					{
						case ilWorkspaceAccessGUI::PERMISSION_ALL:				
							return true;
								
						case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
							// check against input kept in session
							if(self::getSharedNodePassword($a_node_id) == self::getSharedSessionPassword($a_node_id) || 
								$a_permission == "visible")
							{
								return true;
							}
							break;
					
						case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
							if($ilUser->getId() != ANONYMOUS_USER_ID)
							{
								return true;
							}
							break;
								
						default:
							switch(ilObject::_lookupType($obj_id))
							{
								case "grp":
									// member of group?
									if(ilGroupParticipants::_getInstanceByObjId($obj_id)->isAssigned($a_user_id))
									{
										return true;
									}
									break;

								case "crs":
									// member of course?
									if(ilCourseParticipants::_getInstanceByObjId($obj_id)->isAssigned($a_user_id))
									{
										return true;
									}
									break;

								case "role":
									// has role?
									if($rbacreview->isAssigned($a_user_id, $obj_id))
									{
										return true;
									}
									break;

								case "usr":
									// direct assignment
									if($a_user_id == $obj_id)
									{
										return true;
									}
									break;
							}
							break;
					}
				}
			}
		}
		
		return false;
	}

	/**
	 * Set permissions after creating node/object
	 * 
	 * @param int $a_parent_node_id
	 * @param int $a_node_id
	 */
	public function setPermissions($a_parent_node_id, $a_node_id)
	{
		// nothing to do as owner has irrefutable rights to any portfolio object
	}

	/**
	 * Add permission to node for object
	 *
	 * @param int $a_node_id
	 * @param int $a_object_id
	 * @param string $a_extended_data
	 */
	public function addPermission($a_node_id, $a_object_id, $a_extended_data = null)
	{
		global $ilDB, $ilUser;

		// current owner must not be added
		if($a_object_id == $ilUser->getId())
		{
			return;
		}

		$ilDB->manipulate("INSERT INTO usr_portf_acl (node_id, object_id, extended_data)".
			" VALUES (".$ilDB->quote($a_node_id, "integer").", ".
			$ilDB->quote($a_object_id, "integer").",".
			$ilDB->quote($a_extended_data, "text").")");
	}

	/**
	 * Remove permission[s] (for object) to node
	 *
	 * @param int $a_node_id
	 * @param int $a_object_id 
	 */
	public function removePermission($a_node_id, $a_object_id = null)
	{
		global $ilDB;
		
		$query = "DELETE FROM usr_portf_acl".
			" WHERE node_id = ".$ilDB->quote($a_node_id, "integer");

		if($a_object_id)
		{
			$query .= " AND object_id = ".$ilDB->quote($a_object_id, "integer");
		}

		return $ilDB->manipulate($query);
	}

	/**
	 * Get all permissions to node
	 *
	 * @param int $a_node_id
	 * @return array
	 */
	public function getPermissions($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT object_id FROM usr_portf_acl".
			" WHERE node_id = ".$ilDB->quote($a_node_id, "integer"));
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["object_id"];
		}
		return $res;
	}
	
	public function hasRegisteredPermission($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT object_id FROM usr_portf_acl".
			" WHERE node_id = ".$ilDB->quote($a_node_id, "integer").
			" AND object_id = ".$ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_REGISTERED, "integer"));
		return (bool)$ilDB->numRows($set);
	}
	
	public function hasGlobalPermission($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT object_id FROM usr_portf_acl".
			" WHERE node_id = ".$ilDB->quote($a_node_id, "integer").
			" AND object_id = ".$ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL, "integer"));
		return (bool)$ilDB->numRows($set);
	}
	
	public function hasGlobalPasswordPermission($a_node_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT object_id FROM usr_portf_acl".
			" WHERE node_id = ".$ilDB->quote($a_node_id, "integer").
			" AND object_id = ".$ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
		return (bool)$ilDB->numRows($set);
	}
	
	public function getObjectsIShare()
	{
		global $ilDB, $ilUser;
		
		$res = array();
		$set = $ilDB->query("SELECT obj.obj_id".
			" FROM object_data obj".
			" JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)".
			" WHERE obj.owner = ".$ilDB->quote($ilUser->getId(), "integer"));
		while ($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["obj_id"];
		}			
		
		return $res;
	}
	
	public static function getPossibleSharedTargets()
	{
		global $ilUser;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
		include_once "Services/Membership/classes/class.ilParticipants.php";
		$grp_ids = ilParticipants::_getMembershipByType($ilUser->getId(), "grp");
		$crs_ids = ilParticipants::_getMembershipByType($ilUser->getId(), "crs");
		
		$obj_ids = array_merge($grp_ids, $crs_ids);
		$obj_ids[] = $ilUser->getId();
		$obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_REGISTERED;		
		$obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL;
		$obj_ids[] = ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD;

		return $obj_ids;
	}
	
	public function getSharedOwners()
	{
		global $ilUser, $ilDB;
		
		$obj_ids = $this->getPossibleSharedTargets();
		
		$user_ids = array();
		$set = $ilDB->query("SELECT DISTINCT(obj.owner), u.lastname, u.firstname, u.title".
			" FROM object_data obj".
			" JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)".
			" JOIN usr_data u on (u.usr_id = obj.owner)".
			" WHERE ".$ilDB->in("acl.object_id", $obj_ids, "", "integer").
			" AND obj.owner <> ".$ilDB->quote($ilUser->getId(), "integer").
			" ORDER BY u.lastname, u.firstname, u.title");
		while ($row = $ilDB->fetchAssoc($set))
		{
			$user_ids[$row["owner"]] = $row["lastname"].", ".$row["firstname"];
			if($row["title"])
			{
				$user_ids[$row["owner"]] .= ", ".$row["title"];
			}
		}
		
		return $user_ids;
	}
	
	public function getSharedObjects($a_owner_id)
	{
		global $ilDB;
		
		$obj_ids = $this->getPossibleSharedTargets();
		
		$res = array();
		$set = $ilDB->query("SELECT obj.obj_id".
			" FROM object_data obj".
			" JOIN usr_portf_acl acl ON (acl.node_id = obj.obj_id)".
			" WHERE ".$ilDB->in("acl.object_id", $obj_ids, "", "integer").
			" AND obj.owner = ".$ilDB->quote($a_owner_id, "integer"));
		while ($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["obj_id"]] = $row["obj_id"];
		}
	
		return $res;
	}
	
	public static function getSharedNodePassword($a_node_id)
	{
		global $ilDB;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
		
		$set = $ilDB->query("SELECT extended_data FROM usr_portf_acl".
			" WHERE node_id = ".$ilDB->quote($a_node_id, "integer").
			" AND object_id = ".$ilDB->quote(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, "integer"));
		$res = $ilDB->fetchAssoc($set);
		if($res)
		{
			return $res["extended_data"];
		}
	}
	
	public static function keepSharedSessionPassword($a_node_id, $a_password) 
	{
		$_SESSION["ilshpw_".$a_node_id] = $a_password;
	}
	
	public static function getSharedSessionPassword($a_node_id)
	{
		return $_SESSION["ilshpw_".$a_node_id];
	}
}

?>