<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */#

/**
* Request for the creation of a decentral training.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevDecentralTrainingCreationRequest {
	// @var int				Id of the user that wants to create the training.
	protected $user_id;
	
	// @var int				Object id of the course template to use.
	protected $template_obj_id;
	
	// @var gevDecentralTrainingSettings
	protected $settings;
	
	// @var int[]			Ids of the trainers that should be added to th course.
	protected $trainer_ids;
	
	// @var ilDateTime|null	Datetime the creation was requested.
	protected $requested_ts;
	// @var ilDateTime|null	Datetime the creation was requested.
	protected $finished_ts;
	
	// @var int|null		Id of the object that was created by the request.
	protected $created_obj_id;
	
	public function __construct( $a_user_id
							   , $a_template_obj_id
							   , array $a_trainer_ids
							   , gevDecentralTrainingSettings $a_settings
							   ) {
		// TODO: Maybe uncomment this, if the stuff works.
		assert(is_int($a_user_id));
		assert(ilObject::_lookupType($a_user_id) == "usr");
		
		assert(is_int($a_template_obj_id));
		assert(ilObject::_lookupType($a_template_obj_id) == "crs");
		
		foreach ($a_trainer_ids as $id) {
			assert(is_int($id));
			assert(ilObject::_lookupType($id) == "usr");
		}
		
		$this->user_id = $a_user_id;
		$this->template_obj_id = $a_template_obj_id;
		$this->trainer_ids = $a_trainer_ids;
		$this->settings = $a_settings;
		$this->requested_ts = null;
		$this->finished_ts = null;
	}
	
	protected function getCourseUtils($a_obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		return gevCourseUtils::getInstance($a_obj_id);
	}
	
	protected function throwException($msg) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingException.php");
		throw new gevDecentralTrainingException($msg);
	}
	
	public function run() {
		if ($this->finished_ts !== null) {
			$this->throwException("Request already finished.");
		}
		
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$tree = $this->getTree();
		$db = $this->getDB();
		$rbacreview = $this->getRBACReview();
		$object_factory = $this->getObjectFactory();
		$rbacadmin = $this->getRBACAdmin();
		$rbacsystem = $this->getRBACSystem();
		$lng = $this->getLng();
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		foreach ($this->trainer_ids as $trainer_id) {
			if (!$dec_utils->canCreateFor($this->user_id, $trainer_id)) {
				$this->throwException( "gevDecentralTrainingUtils::create: No permission"
									  ." for ".$this->user_id
									  ." to create training for ".$trainer_id);
			}
		}
		
		$info = $dec_utils->getTemplateInfoFor($this->user_id, $this->template_obj_id);
		$parent = $tree->getParentId($info["ref_id"]);
		
		$res = $db->query(
			 "SELECT DISTINCT c.child ref_id, od.type "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE p.child = ".$db->quote($info["ref_id"], "integer")
			);
	
		$options = array();
		while($rec = $db->fetchAssoc($res)) {
			if ($type == "rolf") {
				continue;
			}
			$options[$rec["ref_id"]] = array("type" => 2);
		}
		
		$src_utils = gevCourseUtils::getInstance($this->template_obj_id);

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
			$this->throwException("gevDecentralTrainingUtils::create: <br />"
								 ."User has no permission to create training in the category with ref_id = ".$parent
								 ." or user has no permission to copy template course with ref_id = ".$info["ref_id"]
								 ." or anything unexpected happens in gevDecentralTrainingUtils::create.");
		}
		
		$trgt_obj_id = gevObjectUtils::getObjId($trgt_ref_id);
		$trgt_utils = gevCourseUtils::getInstance($trgt_obj_id);
		$trgt_crs = $trgt_utils->getCourse();
		$trgt_crs->setOfflineStatus(false);
		$trgt_crs->update();
		
		
		// Roles and Members
		
		$rolf_data = $rbacreview->getRoleFolderOfObject($trgt_ref_id);
		$rolf = $object_factory->getInstanceByRefId($rolf_data["ref_id"]);
		$creator_role = $rolf->createRole( $lng->txt("gev_dev_training_creator")
										 , sprintf($lng->txt("gev_dev_training_creator_desc"), $trgt_ref_id)
										 );
		
		$res = $db->query( "SELECT obj_id FROM object_data "
						  ."WHERE type = 'rolt'"
						  ."  AND title = ".$db->quote($lng->txt("gev_dev_training_creator"), "text")
						  );
		if ($rec = $db->fetchAssoc($res)) {
			$rbacadmin->copyRoleTemplatePermissions(
							$rec["obj_id"], ROLE_FOLDER_ID
							, $rolf->getRefId(), $creator_role->getId());
			$ops = $rbacreview->getOperationsOfRole($creator_role->getId(), "crs", $rolf->getRefId());
			$rbacadmin->grantPermission($creator_role->getId(), $ops, $trgt_ref_id);
			if (!in_array($this->user_id,$this->trainer_ids)) {
				$rbacadmin->assignUser($creator_role->getId(), $this->user_id);
			}
		}
		else {
			$this->throwException( "gevDecentralTrainingUtils::create: Roletemplate '"
								  .$lng->txt("gev_dev_training_creator")
								  ."' does not exist.");
		}
		
		$trainer_role = $trgt_crs->getDefaultTutorRole();
		$trainer_ops = $rbacreview->getRoleOperationsOnObject(
				$trainer_role,
				$trgt_ref_id
			);
		$revoke_ops = ilRbacReview::_getOperationIdsByName(array("write", "copy", "edit_learning_progress"));
		$grant_ops = ilRbacReview::_getOperationIdsByName(array("book_users", "cancel_bookings", "view_bookings"));
		$new_trainer_ops = array_unique(array_merge($grant_ops, array_diff($trainer_ops, $revoke_ops)));
		$rbacadmin->revokePermission($trgt_ref_id, $trainer_role);
		$rbacadmin->grantPermission($trainer_role, $new_trainer_ops, $trgt_ref_id);

		$orig_admin_id = $src_utils->getMainAdmin()->getId();
		$trgt_crs->setOwner($orig_admin_id);
		$trgt_crs->updateOwner();
		
		$trgt_crs->setTitle($src_utils->getTitle());
		$trgt_crs->update();
		$trgt_crs->getMembersObject()->add($orig_admin_id, IL_CRS_ADMIN);
		$trgt_crs->getMembersObject()->delete($this->user_id);
		
		foreach ($this->trainer_ids as $trainer_id) {
			$trgt_crs->getMembersObject()->add($trainer_id,IL_CRS_TUTOR);
		}
		
		$rbacsystem->resetRoleCache();
		
		$this->settings->applyTo($trgt_obj_id);
		
		return array("ref_id" => $trgt_ref_id, "obj_id" => $trgt_obj_id);
	}
	
	// GETTERS FOR GLOBALS
	
	protected function getTree() {
		global $tree;
		return $tree;
	}
	
	protected function getDB() {
		global $ilDB;
		return $ilDB;
	}
	
	protected function getRBACReview() {
		global $rbacreview;
		return $rbacreview;
	}
	
	protected function getRBACAdmin() {
		global $rbacadmin;
		return $rbacadmin;
	}
	
	protected function getRBACSystem() {
		global $rbacsystem;
		return $rbacsystem;
	}
	
	protected function getObjectFactory() {
		global $ilias;
		return $ilias->obj_factory;
	}
	
	protected function getLng() {
		global $lng;
		return $lng;
	}
	

}
