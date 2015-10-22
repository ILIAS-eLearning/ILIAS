<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */#

/**
* Request for the creation of a decentral training.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevDecentralTrainingCreationRequest {
	const CREATOR_ROLE_TITLE = "Trainingsersteller";
	const CREATOR_ROLE_DESC = "Ersteller des dezentralen Trainings mit Ref-Id %d";
	
	// @var gevDecentralTrainingCreationRequestDB
	protected $db;
	
	// @var int				Id of this request.
	protected $request_id;
	
	// @var int				Id of the user that wants to create the training.
	protected $user_id;
	
	// @var string			Session id to be used for copy soap calls.
	protected $session_id;
	
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
	
	public function __construct( gevDecentralTrainingCreationRequestDB $db
							   , $a_user_id
							   , $a_template_obj_id
							   , array $a_trainer_ids
							   , gevDecentralTrainingSettings $a_settings
							   // For creation from the database.
							   , $a_request_id = null
							   , $a_session_id = null
							   , ilDateTime $a_requested_ts = null
							   , ilDateTime $a_finished_ts = null
							   , $a_created_obj_id = null
							   ) {
		$this->db = $db;
		
		// TODO: Maybe uncomment this, if the stuff works.
		assert($a_request_id === null || is_int($a_request_id));
		assert($a_created_obj_id === null || (is_int($a_created_obj_id)));
		
		assert(is_int($a_user_id));
		assert(ilObject::_lookupType($a_user_id) == "usr");
		
		assert(is_int($a_template_obj_id));
		assert(ilObject::_lookupType($a_template_obj_id) == "crs");
		
		assert($a_session_id === null || is_string($a_session_id));
		
		foreach ($a_trainer_ids as $id) {
			assert(is_int($id));
			assert(ilObject::_lookupType($id) == "usr");
		}
		
		$this->request_id = $a_request_id;
		$this->user_id = $a_user_id;
		$this->session_id = $a_session_id;
		$this->template_obj_id = $a_template_obj_id;
		$this->trainer_ids = $a_trainer_ids;
		$this->settings = $a_settings;
		$this->requested_ts = $a_requested_ts;
		$this->finished_ts = $a_finished_ts;
		$this->created_obj_id = $a_created_obj_id;
	}
	
	public function requestId() {
		return $this->request_id;
	}
	
	public function userId() {
		return $this->user_id;
	}
	
	public function sessionId() {
		return $this->session_id;
	}
	
	public function templateObjId() {
		return $this->template_obj_id;
	}
	
	public function settings() {
		return $this->settings;
	}

	public function setSettings($settings) {
		$this->settings = $settings;
	}
	
	public function trainerIds() {
		return $this->trainer_ids;
	}
	
	public function requestedTS() {
		return $this->requested_ts;
	}
	
	public function finishedTS() {
		return $this->finished_ts;
	}
	
	public function createdObjId() {
		return $this->created_obj_id;
	}
	
	public function request() {
		$this->requested_ts = new ilDateTime(time(),IL_CAL_UNIX);
		$this->session_id = $this->getNewSessionId();
		if ($this->request_id === null) {
			$this->request_id = $this->db->createRequest($this);
		}
		else {
			$this->db->updateRequest($this);
		}
	}
	
	public function save() {
		if ($this->request_id === null) {
			$this->request_id = $this->db->createRequest($this);
		}
		else {
			$this->db->updateRequest($this);
		}
	}
	
	public function delete() {
		$this->db->deleteRequest($this);
		$this->request_id = null;
	}
	
	public function run() {
		if ($this->finished_ts !== null) {
			$this->throwException("Request already finished.");
		}
		
		if ($this->isSessionExpired()) {
			$this->throwException("Session '".$this->session_id."' is expired.");
		}
		
		$rbacsystem = $this->getRBACSystem();
		
		$this->checkPermissionToCreateTrainingForTrainers();

		$src_utils = $this->getCourseUtils($this->template_obj_id);
		
		$trgt_ref_id = $this->cloneTemplate($src_utils->getCourse());
		
		if (!$trgt_ref_id) {
			$this->throwException("gevDecentralTrainingUtils::create:\n"
								 ."User ".$this->userId()."has no permission to create training in the category above the template course"
								 ." or user has no permission to copy template course with obj_id = ".$this->template_obj_id
								 ." or anything unexpected happens in gevDecentralTrainingUtils::create.\n"
								 ."  Request Data:\n"
								 ."     Id:            ".$this->requestId()."\n"
								 ."     UserId:        ".$this->userId()."\n"
								 ."     SessionId:     ".$this->sessionId()."\n"
								 ."     TemplateObjId: ".$this->templateObjId()."\n"
								 );
		}
		
		$trgt_obj_id = $this->getObjectIdFor($trgt_ref_id);
		$trgt_utils = $this->getCourseUtils((int)$trgt_obj_id);
		$trgt_crs = $trgt_utils->getCourse();
		
		// Roles and Members
		$creator_role_id = $this->createCreatorRole($trgt_ref_id);
		$this->adjustTrainerPermissions($trgt_crs);
		$this->adjustOwnerAndAdmin($src_utils, $trgt_crs);
		$this->assignTrainers($trgt_crs);
		$this->maybeAssignCreatorToCreatorRole($creator_role_id);
		
		$rbacsystem->resetRoleCache();
		
		// New course should have same title as old course.
		$trgt_crs->setTitle($src_utils->getTitle());
		// New course should be online.
		$trgt_crs->setOfflineStatus(false);
		$trgt_crs->update();

		$this->settings->applyTo((int)$trgt_obj_id);

		if($trgt_utils->isFlexibleDecentrallTraining()) {
			$this->updateCourseBuildingBlocks($trgt_utils->getRefId());
			$this->updateCourseWithBuidlingBlockData($trgt_utils->getRefId());
		}

		$trgt_crs = $trgt_utils->getCourse();
		
		$this->createTEPEntry($trgt_crs);
		
		$this->finished_ts = new ilDateTime(time(),IL_CAL_UNIX);
		$this->created_obj_id = $trgt_obj_id;
		
		$this->db->updateRequest($this);
		$this->destroySession();
		$this->resetCopyWizard();
	}
	
	public function abort() {
		if ($this->finished_ts !== null) {
			$this->throwException("Request already finished.");
		}
		
		$this->finished_ts = new ilDateTime(time(),IL_CAL_UNIX);
		$this->db->updateRequest($this);
	}
	
	protected function checkPermissionToCreateTrainingForTrainers() {
		$dec_utils = $this->getDecentralTrainingUtils();
		foreach ($this->trainer_ids as $trainer_id) {
			if (!$dec_utils->canCreateFor($this->user_id, $trainer_id)) {
				$this->throwException( "gevDecentralTrainingUtils::create: No permission"
									  ." for ".$this->user_id
									  ." to create training for ".$trainer_id);
			}
		}
	}
	
	protected function cloneTemplate(ilObjCourse $a_src) {
		$db = $this->getDB();
		$tree = $this->getTree();
		$dec_utils = $this->getDecentralTrainingUtils();
		
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
		
		// These are options that tell the cloning method, that every child of the
		// template should be cloned as well.
		$options = array();
		while($rec = $db->fetchAssoc($res)) {
			if ($type == "rolf") {
				continue;
			}
			$options[$rec["ref_id"]] = array("type" => 2);
		}
		
		return $a_src->cloneAllObject( $this->session_id
									 , $this->getClientId()
									 , "crs"
									 , $parent
									 , $info["ref_id"]
									 , $options
									 , false
									 , true
									 , $this->user_id
									 , 600
									 );
	}
	
	protected function createCreatorRole($a_trgt_ref_id) {
		$rbacreview = $this->getRBACReview();
		$object_factory = $this->getObjectFactory();
		$rbacadmin = $this->getRBACAdmin();
		$db = $this->getDB();
		
		// Get role template id
		$res = $db->query( "SELECT obj_id FROM object_data "
						  ."WHERE type = 'rolt'"
						  ."  AND title = ".$db->quote(self::CREATOR_ROLE_TITLE, "text")
						  );
		
		if ($rec = $db->fetchAssoc($res)) {
			// Create the creator role
			$rolf_data = $rbacreview->getRoleFolderOfObject($a_trgt_ref_id);
			$rolf = $object_factory->getInstanceByRefId($rolf_data["ref_id"]);
			$creator_role = $rolf->createRole( self::CREATOR_ROLE_TITLE
											 , sprintf(self::CREATOR_ROLE_DESC, $trgt_ref_id)
											 );
			
			// Adjust permissions according to role template. 
			$rbacadmin->copyRoleTemplatePermissions
							( $rec["obj_id"], ROLE_FOLDER_ID
							, $rolf->getRefId()
							, $creator_role->getId()
							);
			// TODO: This seems to be superfluous, but there also might be a reason this is here...
			$ops = $rbacreview->getOperationsOfRole($creator_role->getId(), "crs", $rolf->getRefId());
			$rbacadmin->grantPermission($creator_role->getId(), $ops, $a_trgt_ref_id);
		}
		else {
			$this->throwException( "gevDecentralTrainingUtils::create: Roletemplate '"
								  .self::CREATOR_ROLE_TITLE
								  ."' does not exist.");
		}
		
		return $creator_role->getId();
	}
	
	protected function maybeAssignCreatorToCreatorRole($creator_role_id) {
		$rbacadmin = $this->getRBACAdmin();
		if (!in_array($this->user_id,$this->trainer_ids)) {
			$rbacadmin->assignUser($creator_role_id, $this->user_id);
		}
	}
	
	protected function adjustTrainerPermissions(ilObjCourse $a_trgt_crs) {
		$rbacreview = $this->getRBACReview();
		$rbacadmin = $this->getRBACAdmin();

		$trainer_role = $a_trgt_crs->getDefaultTutorRole();
		$trainer_ops = $rbacreview->getRoleOperationsOnObject($trainer_role, $a_trgt_crs->getRefId());
		$revoke_ops = $this->getOperationIdsByNames(array("write", "copy", "edit_learning_progress"));
		$grant_ops = $this->getOperationIdsByNames(array("book_users", "cancel_bookings", "view_bookings"));
		$new_trainer_ops = array_unique(array_merge($grant_ops, array_diff($trainer_ops, $revoke_ops)));
		$rbacadmin->revokePermission($a_trgt_crs->getRefId(), $trainer_role);
		$rbacadmin->grantPermission($trainer_role, $new_trainer_ops, $a_trgt_crs->getRefId());
	}
	
	protected function adjustOwnerAndAdmin(gevCourseUtils $a_src_utils, ilObjCourse $a_trgt_crs) {
		$orig_admin_id = $a_src_utils->getMainAdmin()->getId();
		$a_trgt_crs->setOwner($orig_admin_id);
		$a_trgt_crs->updateOwner();
		$a_trgt_crs->getMembersObject()->add($orig_admin_id, IL_CRS_ADMIN);
		$a_trgt_crs->getMembersObject()->delete($this->user_id);
	}
	
	protected function assignTrainers(ilObjCourse $trgt_crs) {
		foreach ($this->trainer_ids as $trainer_id) {
			$trgt_crs->getMembersObject()->add($trainer_id,IL_CRS_TUTOR);
		}
	}
	
	protected function createTEPEntry(ilObjCourse $trgt_crs) {
		require_once("Services/TEP/classes/class.ilTEPCourseEntries.php");
		ilTEPCourseEntries::$instances = array();
		$tep_entry = ilTEPCourseEntries::getInstance($trgt_crs);
		$tep_entry->updateEntry();
	}
	
	// Some Helpers
	
	protected function getCourseUtils($a_obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		return gevCourseUtils::getInstance($a_obj_id);
	}
	
	protected function getDecentralTrainingUtils() {
		return gevDecentralTrainingUtils::getInstance();
	}
	
	protected function throwException($msg) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingException.php");
		throw new gevDecentralTrainingException($msg);
	}
	
	protected function getObjectIdFor($a_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		return gevObjectUtils::getObjId($a_ref_id);
	}
	
	protected function getOperationIdsByNames(array $names) {
		return  ilRbacReview::_getOperationIdsByName($names);
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
	
	protected function getClientId() {
		global $ilias;
		return $ilias->client_id;
	}
	
	protected function getNewSessionId() {
		require_once("Services/Authentication/classes/class.ilSession.php");
		$session_id = $_COOKIE["PHPSESSID"];
		if (!ilSession::_exists($session_id)) {
			$this->throwException("Session '$session_id' does not exists.");
		}
		$session_id = ilSession::_duplicate($session_id);
		return $session_id;
	}
	
	protected function destroySession() {
		require_once("Services/Authentication/classes/class.ilSession.php");
		ilSession::_destroy($this->session_id);
	}
	
	protected function isSessionExpired() {
		require_once("Services/Authentication/classes/class.ilSession.php");
		return !ilSession::_exists($this->session_id);
	}
	
	protected function resetCopyWizard() {
		require_once("Services/CopyWizard/classes/class.ilCopyWizardOptions.php");
		ilCopyWizardOptions::$instances = null;
	}

	protected function updateCourseBuildingBlocks($a_trgt_crs_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
		gevCourseBuildingBlockUtils::updateCrsBuildungBlocksCrsIdByCrsRequestId($a_trgt_crs_ref_id,$this->request_id);
	}

	protected function updateCourseWithBuidlingBlockData($a_trgt_crs_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
		gevCourseBuildingBlockUtils::courseUpdates($a_trgt_crs_ref_id);
	}
}
