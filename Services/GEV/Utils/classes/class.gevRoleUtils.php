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
		
		gevRoleUtils::getRbacAdmin()->assignUser($roles[$a_role_title], $a_user_id);
	}
	
	public function getGlobalRolesOf($a_user_id) {
		return $this->getRbacReview()->assignedGlobalRoles($a_user_id);
	}
}

?>