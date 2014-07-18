<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for Roles for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevRoleUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		$this->rbac_admin = null;
		$this->rbac_review = null;
		$this->global_roles = null;
		$this->flipped_global_roles = null;
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getRbacAdmin() {
		if ($this->rbac_admin === null) {
			require_once("Services/AccessControl/classes/class.ilRbacAdmin.php");
			$this->rbac_admin = new ilRbacAdmin();
		}
		
		return $this->rbac_admin;
	}
	
	public function getRbacReview() {
		if ($this->rbac_review === null) {
			require_once("Services/AccessControl/classes/class.ilRbacReview.php");
			$this->rbac_review = new ilRbacReview();
		}
		
		return $this->rbac_review;
	}
	
	public function getGlobalRoles() {
		if ($this->global_roles === null) {
			$roles = $this->getRbacReview()->getGlobalRoles();
			
			$res = $this->db->query("SELECT obj_id, title FROM object_data "
								   ." WHERE ".$this->db->in("obj_id", $roles, false, "integer")
								   );
			
			$this->global_roles = array();
			while ($rec = $this->db->fetchAssoc($res)) {
				$this->global_roles[$rec["obj_id"]] = $rec["title"];
			}
		}

		return $this->global_roles;
	}
	
	public function getFlippedGlobalRoles() {
		if ($this->flipped_global_roles === null) {
			$this->flipped_global_roles = array_flip($this->getGlobalRoles());
		}
	
		return $this->flipped_global_roles;
	}
	
	public function assignUserToGlobalRole($a_user_id, $a_role_title) {
		$roles = $this->getFlippedGlobalRoles();
		
		if (!array_key_exists($a_role_title, $roles)) {
			$this->log->write("gevRoleUtils::assignUserToGlobalRole: Could not assign user "
							 .$a_user_id." to unknown role ".$a_role_title);
			return;
		}
		
		$role_id = $roles[$a_role_title];
		gevRoleUtils::getRbacAdmin()->assignUser($role_id, $a_user_id);
		
		global $ilAppEventHandler;
		$ilAppEventHandler->raise('Services/GEV',
			'assignGlobalRole',
			array('user_id' => $a_user_id,
				  'role_id' => $role_id
				  )
			);
	}
	
	public function deassignUserToGlobalRole($a_user_id, $a_role_title) {
		$roles = $this->getFlippedGlobalRoles();
		
		if (!array_key_exists($a_role_title, $roles)) {
			$this->log->write("gevRoleUtils::assignUserToGlobalRole: Could not assign user "
							 .$a_user_id." to unknown role ".$a_role_title);
			return;
		}
		
		$role_id = $roles[$a_role_title];
		gevRoleUtils::getRbacAdmin()->deassignUser($role_id, $a_user_id);
		
		global $ilAppEventHandler;
		$ilAppEventHandler->raise('Services/GEV',
			'deassignGlobalRole',
			array('user_id' => $a_user_id,
				  'role_id' => $role_id
				  )
			);
	}
	
	public function getGlobalRolesOf($a_user_id) {
		return $this->getRbacReview()->assignedGlobalRoles($a_user_id);
	}
	
	public function getLocalRoleIdsAndTitles($a_obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$rbac_review = $this->getRbacReview();

		$rolf = $rbac_review->getRoleFolderOfObject(gevObjectUtils::getRefId($a_obj_id));

		if (!isset($rolf["ref_id"]) or !$rolf["ref_id"]) {
			throw new Exception("gevRoleUtils::getLocalRoleIdsAndTitles: Could not load role folder.");
		}
		
		$roles = $rbac_review->getRolesOfRoleFolder($rolf["ref_id"], false);
		$res = $this->db->query( "SELECT obj_id, title FROM object_data "
								." WHERE ".$this->db->in("obj_id", $roles, false, "integer"));
		$ret = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$ret[$rec["obj_id"]] = $rec["title"];
		}
		return $ret;
	}
}

?>