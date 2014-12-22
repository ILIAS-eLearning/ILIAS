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
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevDecentralTrainingUtils {
	static $instance = null;
	protected $creation_permissions = array();
	protected $creation_users = array();
	
	protected function __construct() {
		global $ilDB, $ilias, $ilLog, $ilAccess, $tree, $lng, $rbacreview, $rbacadmin, $rbacsystem;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		$this->access = &$ilAccess;
		$this->tree = &$tree;
		$this->lng = &$lng;
		$this->rbacreview = &$rbacreview;
		$this->rbacadmin = &$rbacadmin;
		$this->rbacsystem = &$rbacsystem;
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
	
	// PERMISSIONS
	
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
			return count($this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_self")) > 0;
		}
		else {
			return in_array($a_target_user_id, $this->getUsersWhereCanCreateFor($a_user_id));
		}
	}
	
	public function getUsersWhereCanCreateFor($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		if (array_key_exists($a_user_id, $this->creation_users)) {
			return $this->creation_users[$a_user_id];
		}
		
		$orgus_d = $this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_others");
		$orgus_r = $this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_others_rec");
		$orgus_s = gevOrgUnitUtils::getAllChildren($orgus_r);
		foreach ($orgus_s as $key => $value) {
			$orgus_s[$key] = $value["ref_id"];
		}
		
		$orgus = array_unique(array_merge($orgus_d, $orgus_r, $orgus_s));
		
		$this->creation_users[$a_user_id] = gevOrgUnitUtils::getTrainersIn($orgus);
		return $this->creation_users[$a_user_id];
	}
	
	public function canCreate($a_user_id) {
		return	   count($this->getOrgTree()->getOrgusWhereUserHasPermissionForOperation("add_dec_training_self")) > 0
				|| count($this->getUsersWhereCanCreateFor($a_user_id)) > 0;
	}
	
	// TEMPLATES
	
	protected function templateBaseQuery() {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$ltype_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$edu_prog_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_EDU_PROGRAMM);
		$is_tmplt_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		
		return   "SELECT DISTINCT od.obj_id"
				."              , od.title"
				."              , od.description"
				."              , oref.ref_id"
				."				, ltype.value as ltype"
				."  FROM crs_settings cs"
				."  JOIN object_data od ON od.obj_id = cs.obj_id"
				."  LEFT JOIN object_reference oref "
				."    ON cs.obj_id = oref.obj_id "
				."  LEFT JOIN adv_md_values_text edu_prog"
				."    ON cs.obj_id = edu_prog.obj_id"
				."    AND edu_prog.field_id = ".$this->db->quote($edu_prog_field_id, "integer")
				."  LEFT JOIN adv_md_values_text ltype"
				."    ON cs.obj_id = ltype.obj_id"
				."    AND ltype.field_id = ".$this->db->quote($ltype_field_id, "integer")
				."  LEFT JOIN adv_md_values_text is_template"
				."    ON cs.obj_id = is_template.obj_id"
				."    AND is_template.field_id = ".$this->db->quote($is_tmplt_field_id, "integer")
				." WHERE cs.activation_type = 1"
				."   AND oref.deleted IS NULL"
				."   AND is_template.value = 'Ja'"
				."   AND edu_prog.value = 'dezentrales Training'";
	}
	
	public function getAvailableTemplatesFor($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$ltype_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$edu_prog_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_EDU_PROGRAMM);
		$is_tmplt_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		
		$query = $this->templateBaseQuery();
		$res = $this->db->query($query);
		
		$ret = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$parent = $this->tree->getParentId($rec["ref_id"]);
			if (   $this->access->checkAccessOfUser($a_user_id, "visible",  "", $rec["ref_id"], "crs")
				&& $this->access->checkAccessOfUser($a_user_id, "copy", "", $rec["ref_id"], "crs")
				&& $this->access->checkAccessOfUser($a_user_id, "create_crs", "", $parent, "cat")) {
				$ret[$rec["obj_id"]] = $rec;
			}
		}
		
		return $ret;
	}
	
	public function getTemplateInfoFor($a_user_id, $a_template_id) {
		$query = $this->templateBaseQuery()
				."  AND od.obj_id = ".$this->db->quote($a_template_id);
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			if ($this->access->checkAccessOfUser($a_user_id, "visible",  "", $rec["ref_id"], "crs")) {
				return $rec;
			}
		}
		
		// Could also mean that no permission is granted, but we hide that 
		throw new Exception("gevDecentralTrainingUtils::getTemplateInfoFor: Training not found.");
	}
	
	public function create($a_user_id, $a_template_id, $a_trainer_ids) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		
		foreach ($a_trainer_ids as $trainer_id) {
			if (!$this->canCreateFor($a_user_id, $trainer_id)) {
				throw new Exception( "gevDecentralTrainingUtils::create: No permission for ".$a_user_id
									." to create training for ".$trainer_id);
			}
		}
		
		$info = $this->getTemplateInfoFor($a_user_id, $a_template_id);
		$parent = $this->tree->getParentId($info["ref_id"]);
		
		$res = $this->db->query(
			 "SELECT DISTINCT c.child ref_id, od.type "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE p.child = ".$this->db->quote($info["ref_id"], "integer")
			);
	
		$options = array();
		while($rec = $this->db->fetchAssoc($res)) {
			if ($type == "rolf") {
				continue;
			}
			$options[$rec["ref_id"]] = array("type" => 2);
		}
		
		$src_utils = gevCourseUtils::getInstance($a_template_id);

		$trgt_ref_id = $src_utils->getCourse()
						->cloneAllObject( $_COOKIE['PHPSESSID']
										, $_COOKIE['ilClientId']
										, "crs"
										, $parent
										, $info["ref_id"]
										, $options
										, false
										, true
										);
		if (!$trgt_ref_id) {
			throw new Exception("gevDecentralTrainingUtils::create: <br />"
								."User has no permission to create training in the category with ref_id = ".$parent
								." or user has no permission to copy template course with ref_id = ".$info["ref_id"]);
		}
		
		$trgt_obj_id = gevObjectUtils::getObjId($trgt_ref_id);
		$trgt_utils = gevCourseUtils::getInstance($trgt_obj_id);
		$trgt_crs = $trgt_utils->getCourse();
		$trgt_crs->setOfflineStatus(false);
		$trgt_crs->update();
		
		
		// Roles and Members
		
		$rolf_data = $this->rbacreview->getRoleFolderOfObject($trgt_ref_id);
		$rolf = $this->ilias->obj_factory->getInstanceByRefId($rolf_data["ref_id"]);
		$creator_role = $rolf->createRole( $this->lng->txt("gev_dev_training_creator")
										 , sprintf($this->lng->txt("gev_dev_training_creator_desc"), $trgt_ref_id)
										 );
		
		$res = $this->db->query( "SELECT obj_id FROM object_data "
								."WHERE type = 'rolt'"
								."  AND title = ".$this->db->quote($this->lng->txt("gev_dev_training_creator"), "text")
								);
		if ($rec = $this->db->fetchAssoc($res)) {
			$this->rbacadmin->copyRoleTemplatePermissions(
						$rec["obj_id"], ROLE_FOLDER_ID
						, $rolf->getRefId(), $creator_role->getId());
			$ops = $this->rbacreview->getOperationsOfRole($creator_role->getId(), "crs", $rolf->getRefId());
			$this->rbacadmin->grantPermission($creator_role->getId(), $ops, $trgt_ref_id);
			if (!in_array($a_user_id,$a_trainer_ids)) {
				$this->rbacadmin->assignUser($creator_role->getId(), $a_user_id);
			}
		}
		else {
			throw new Exception( "gevDecentralTrainingUtils::create: Roletemplate '"
								.$this->lng->txt("gev_dev_training_creator")
								."' does not exist.");
		}
		
		$trainer_role = $trgt_crs->getDefaultTutorRole();
		$trainer_ops = $this->rbacreview->getRoleOperationsOnObject(
				$trainer_role,
				$trgt_ref_id
			);
		$revoke_ops = ilRbacReview::_getOperationIdsByName(array("write", "copy", "edit_learning_progress", "book_users"));
		$new_trainer_ops = array_diff($trainer_ops, $revoke_ops);
		$this->rbacadmin->revokePermission($trgt_ref_id, $trainer_role);
		$this->rbacadmin->grantPermission($trainer_role, $new_trainer_ops, $trgt_ref_id);

		$orig_admin_id = $src_utils->getMainAdmin()->getId();
		$trgt_crs->setOwner($orig_admin_id);
		$trgt_crs->updateOwner();
		
		$trgt_crs->setTitle($src_utils->getTitle());
		$trgt_crs->update();
		$trgt_crs->getMembersObject()->add($orig_admin_id, IL_CRS_ADMIN);
		$trgt_crs->getMembersObject()->delete($a_user_id);
		
		foreach ($a_trainer_ids as $trainer_id) {
			$trgt_crs->getMembersObject()->add($trainer_id,IL_CRS_TUTOR);
		}
		
		$this->rbacsystem->resetRoleCache();
		
		return array("ref_id" => $trgt_ref_id, "obj_id" => $trgt_obj_id);
	}
}

?>