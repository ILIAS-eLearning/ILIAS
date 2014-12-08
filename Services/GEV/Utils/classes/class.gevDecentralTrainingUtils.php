<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for decentral trainings of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevDecentralTrainingUtils {
	static $instance = null;
	protected $creation_permissions = array();
	protected $creation_users = array();
	
	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevDecentralTrainingUtils();
		}
		
		return self::$instance;
	}
	
	protected function getOrgTree() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		return ilObjOrgUnitTree::_getInstance();
	}
	
	public function canCreateFor($a_user_id, $a_target_user_id) {
		if (!array_key_exists($a_user_id, $this->creation_permissions)) {
			$this->creation_permissions[$a_user_id] = array();
		}
		
		if (!array_key_exists($a_target_user_id, $this->creation_permissions[$a_user_id])) {
			$this->creation_permissions[$a_user_id][$a_target_user_id] = $this->queryCanCreateFor($a_user_id, $a_target_user_id);
		}
		
		return $this->creation_permissions[$a_user_id][$a_target_user_id];
	}
	
	protected function queryCanCreateFor($a_user_id, $a_target_user_id) {
		
		if ($a_user_id == $a_target_user_id) {
			return count($this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_self"));
		}
		else {
			return in_array($a_target_user_id, $this->getUsersWhereCanCreateFor());
		}
	}
	
	protected function getUsersWhereCanCreateFor($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		if (array_key_exists($a_user_id, $this->creation_users)) {
			return $this->creation_users[$a_user_id];
		}
		
		$orgu_utils = gevOrgUnitUtils::getInstance();
		
		$orgus_e1 = $this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_others");
		$orgus_e2 = $this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_others_rec");
		$orgus_e = array_unique(array_merge($orgus_e1, $orgus_e2));
		$orgus_a = $orgu_utils->getAllChildren($orgus_e2);
		foreach ($orgus_a as $key => $value) {
			$orgus_a[$key] = $value["ref_id"];
		}
		
		$this->creation_users[$a_user_id] = 
			array_merge(  $orgu_utils->getEmployeesIn($orgus_e)
						, $orgu_utils->getAllPeopleIn($orgus_a)
						);
		return $this->creation_users[$a_user_id];
	}
}

?>