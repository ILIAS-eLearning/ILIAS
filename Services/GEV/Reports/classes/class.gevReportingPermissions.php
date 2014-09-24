<?php
/* Copyright (c) 2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

/**
 * TEP permissions handling
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */

class gevReportingPermissions
{	
	protected $user_id; // [int]
	protected $perms; // [array]
	
	static protected $instances = array();
	
	/**
	 * Constructor
	 * 
	 * @param int $a_user_id
	 * @return self
	 */
	protected function __construct($a_user_id)
	{	
		$this->user_id = (int)$a_user_id;
		$this->user_utils = gevUserUtils::getInstance($this->user_id);
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_user_id
	 * @return self
	 */
	public static function getInstance($a_user_id = null)
	{				
		global $ilUser;
		
		$a_user_id = (int)$a_user_id;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		else if(ilObject::_lookupType($a_user_id) != "usr") 
		{			
			throw new ilException("gevReportingPermissions - needs user id");		
		}
		
		if($a_user_id == ANONYMOUS_USER_ID)
		{
			throw new ilException("gevReportingPermissions - cannot handle anonymous user");
		}		
		
		if(!array_key_exists($a_user_id, self::$instances))
		{
			self::$instances[$a_user_id] = new self($a_user_id);
		}
		
		return self::$instances[$a_user_id];
	}
	
	
	//
	// properties
	//

	/**
	 * Get user id
	 * 
	 * @return int
	 */	
	public function getUserId()
	{
		return $this->user_id;
	}
	
	
	//
	// Permissions
	//
	
	public function viewAnyReport() {
		return $this->viewBillingReport();
	}
	
	public function viewBillingReport() {
		return $this->user_utils->hasRoleIn(array("Buchhaltung"));
	}
	
	//
	// org unit
	// 
	
	/**
	 * Load org unit data
	 * 
	 * @param int $a_user_id
	 * @return array
	 */
	/*protected function loadOrgUnitPermissions($a_user_id)
	{
		global $rbacsystem;
		
		$res = array();
				
		$ou_tree = ilObjOrgUnitTree::_getInstance();				
		foreach($ou_tree->getOrgUnitOfUser($a_user_id) as $ou_ref_id)
		{
			$res[$ou_ref_id]["tep_is_tutor"] = $rbacsystem->checkAccessOfUser($a_user_id, "tep_is_tutor", $ou_ref_id);
			$res[$ou_ref_id]["tep_view_other"] = $rbacsystem->checkAccessOfUser($a_user_id, "tep_view_other", $ou_ref_id);
			$res[$ou_ref_id]["tep_view_other_rcrsv"] = $rbacsystem->checkAccessOfUser($a_user_id, "tep_view_other_rcrsv", $ou_ref_id);
			$res[$ou_ref_id]["tep_edit_other"] = $rbacsystem->checkAccessOfUser($a_user_id, "tep_edit_other", $ou_ref_id);
			$res[$ou_ref_id]["tep_edit_other_rcrsv"] = $rbacsystem->checkAccessOfUser($a_user_id, "tep_edit_other_rcrsv", $ou_ref_id);			
		}
		
		return $res;
	}*/
	
	/**
	 * Get all org units BELOW given units
	 * 
	 * @param array $ou_ref_ids
	 * @return array
	 */
	/*protected function getRecursiveOrgUnits(array $ou_ref_ids)
	{		
		$ou_tree = ilObjOrgUnitTree::_getInstance();	
		foreach($ou_tree->getAllChildren(ilObjOrgUnit::getRootOrgRefId()) as $ou_ref_id)
		{				
			if(in_array($ou_ref_id, $ou_ref_ids))
			{
				continue;
			}
			else
			{
				$parent = $ou_tree->getParent($ou_ref_id);
				while($parent)
				{					
					if(in_array($parent, $ou_ref_ids))
					{
						$ou_ref_ids[] = (int)$ou_ref_id;
						break;
					}					
					$parent = $ou_tree->getParent($parent);
				}				
			}			
		}	
		
		return $ou_ref_ids;
	}*/
	
	/**
	 * Get users with tutor-permission in given org units
	 * 
	 * @param array $ou_ref_ids
	 * @return array
	 */
	/*protected function getOrgUnitTutors(array $ou_ref_ids)
	{
		global $ilDB;
		
		$res = array();
		
		// only interested in 1 permission
		$sql = "SELECT ops_id FROM rbac_operations".
			" WHERE operation = ".$ilDB->quote("tep_is_tutor", "text");
		$set = $ilDB->query($sql);		
		if($ilDB->numRows($set))
		{
			$ops_id = $ilDB->fetchAssoc($set);
			$ops_id = $ops_id["ops_id"];
			
			// get all roles for given org units with matching permission
			$sql = "SELECT rol_id, ops_id".
				" FROM rbac_pa".
				" WHERE ".$ilDB->in("ref_id", array_unique($ou_ref_ids), "", "integer");		
			$set = $ilDB->query($sql);
			$rol_ids = array();
			while($row = $ilDB->fetchAssoc($set))
			{								
				// this is needed as the table rbac_operations is not in the first normal form, thus this needs some additional checkings.
				$perm_check = unserialize($row["ops_id"]);				
				if(is_array($perm_check) &&
					in_array($ops_id, $perm_check))
				{
					$rol_ids[] = $row["rol_id"];
				}
			}
									
			// get all role members
			if(sizeof($rol_ids))
			{
				$sql = "SELECT usr_id".
					" FROM rbac_ua".
					" WHERE ".$ilDB->in("rol_id", $rol_ids, "", "integer");		
				$set = $ilDB->query($sql);
				while($row = $ilDB->fetchAssoc($set))
				{			
					$res[] = $row["usr_id"];
				}
			}
		}
		
		return $res;
	}*/
	
	
	//
	// permissions
	// 
	
	/**
	 * Check if user has permission in any org unit
	 * 
	 * @param string $a_permission
	 * @return boolean
	 */
	/*protected function hasPermissionInAnyOrgUnit($a_permission)
	{
		foreach($this->perms as $ou_perms)
		{
			if($ou_perms[$a_permission])
			{
				return true;
			}
		}
		return false;
	}*/
	
	/**
	 * Is user tutor in any org-unit?
	 * 
	 * @return bool
	 */
	/*public function isTutor()
	{
		return $this->hasPermissionInAnyOrgUnit("tep_is_tutor");
	}*/
	
	/**
	 * Get org units where user can view others
	 * 
	 * @return array
	 */
	/*public function getViewOtherOrgUnits()
	{
		$res = array();
		
		foreach($this->perms as $ou_ref_id => $ou_perms)
		{
			if($ou_perms["tep_view_other"])
			{
				$res[] = $ou_ref_id;
			}
		}
		
		return $res;
	}*/
	
	/**
	 * Get tutors from org units where user can view others
	 * 
	 * @param array $a_org_ref_ids
	 * @return array
	 */
	/*public function getViewOtherUserIds(array $a_org_ref_ids = null)
	{
		$valid = $this->getViewOtherOrgUnits();
		if(!$a_org_ref_ids)
		{
			$a_org_ref_ids = $valid;
		}
		else
		{
			$a_org_ref_ids = array_intersect($a_org_ref_ids, $valid);
		}		
		return $this->getOrgUnitTutors($a_org_ref_ids);
	}*/
	
	/**
	 * Get org units where user can view other tutors recursively
	 * 
	 * @param array $a_org_ref_ids
	 * @return array
	 */
	/*public function getViewOtherRecursiveOrgUnits(array $a_org_ref_ids = null)
	{
		$ou_ref_ids = array();
		
		foreach($this->perms as $ou_ref_id => $ou_perms)
		{
			if(!is_array($a_org_ref_ids) || 
				in_array($ou_ref_id, $a_org_ref_ids))
			{
				if($ou_perms["tep_view_other_rcrsv"])
				{
					$ou_ref_ids[] = $ou_ref_id;
				}
			}
		}
		
		return $this->getRecursiveOrgUnits($ou_ref_ids);
	}*/
	
	/**
	 * Get tutors from org units where user can view others recursively
	 * 
	 * @param array $a_org_ref_ids
	 * @return array
	 */
	/*public function getViewOtherRecursiveUserIds(array $a_org_ref_ids = null)
	{
		return $this->getOrgUnitTutors($this->getViewOtherRecursiveOrgUnits($a_org_ref_ids));
	}*/
	
	
	/**
	 * Get org units where user can edit others
	 * 
	 * @return array
	 */
	/*public function getEditOtherOrgUnits()
	{
		$ou_ref_ids = array();
		
		foreach($this->perms as $ou_ref_id => $ou_perms)
		{
			if($ou_perms["tep_edit_other"])
			{
				$ou_ref_ids[] = $ou_ref_id;
			}
		}
		
		return $this->getRecursiveOrgUnits($ou_ref_ids);
	}*/
	
	/**
	 * Get tutors from org units where user can edit others
	 * 
	 * @return array
	 */
	/*public function getEditOtherUserIds()
	{
		return $this->getOrgUnitTutors($this->getEditOtherOrgUnits());
	}*/
	
	/**
	 * Get org units where user can edit other tutors recursively
	 * 
	 * @return array
	 */
	/*public function getEditOtherRecursiveOrgUnits()
	{
		$ou_ref_ids = array();
		
		foreach($this->perms as $ou_ref_id => $ou_perms)
		{
			if($ou_perms["tep_edit_other_rcrsv"])
			{
				$ou_ref_ids[] = $ou_ref_id;
			}
		}
		
		return $this->getRecursiveOrgUnits($ou_ref_ids);
	}*/
	
	/**
	 * Get tutors from org units where user can edit others recursively
	 * 
	 * @return array
	 */
	/*public function getEditOtherRecursiveUserIds()
	{		
		return $this->getOrgUnitTutors($this->getEditOtherRecursiveOrgUnits());
	}*/
	
	//
	// meta
	// 
	
	/**
	 * May user view "anything"?
	 * 
	 * @return bool
	 */
	/*public function mayView()
	{		
		return ($this->isTutor() || 
			$this->hasPermissionInAnyOrgUnit("tep_view_other") ||
			$this->hasPermissionInAnyOrgUnit("tep_view_other_rcrsv"));
	}*/
	
	/**
	 * May user edit "anything"?
	 * 
	 * @return bool
	 */
	/*public function mayEdit()
	{
		return ($this->isTutor() || 
			$this->mayEditOthers());
	}*/
	
	/**
	 * May user edit anyone else?
	 * 
	 * @return bool
	 */
	/*public function mayEditOthers()
	{
		return ($this->hasPermissionInAnyOrgUnit("tep_edit_other") ||
			$this->hasPermissionInAnyOrgUnit("tep_edit_other_rcrsv"));
	}*/
	
	/**
	 * May user view anyone else?
	 * 
	 * @return bool
	 */
	/*public function mayViewOthers()
	{
		return ($this->hasPermissionInAnyOrgUnit("tep_view_other") ||
			$this->hasPermissionInAnyOrgUnit("tep_view_other_rcrsv"));
	}*/


/*

	public function getOrgUnitIdsWhereUserHasRole(array $possibleRoles, $recursive=false) 
	{
		//current userId: $this->user_id

		$valid_ou_ref_ids = array();
		
		$ou_tree = ilObjOrgUnitTree::_getInstance();
		$ou_ref_ids = $ou_tree->getOrgUnitOfUser($this->user_id);

		foreach ($ou_ref_ids as $ou_ref_id) {
			//check, if user has a valid role in there.
			//TODO: getRole and check
			$valid_ou_ref_ids[] = $ou_ref_id;
		}

		if($recursive) {
			$valid_ou_ref_ids = $this->getRecursiveOrgUnits($valid_ou_ref_ids);
		}

		
		return $valid_ou_ref_ids;
	}

	
*/

}
