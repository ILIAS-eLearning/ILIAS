<?php
require_once 'Modules/IndividualAssessment/interfaces/AccessControl/interface.IndividualAssessmentAccessHandler.php';
require_once 'Services/AccessControl/classes/class.ilObjRole.php';
/**
 * @inheritdoc
 * Deal with ilias rbac-system
 */
class ilIndividualAssessmentAccessHandler implements IndividualAssessmentAccessHandler {

	/**
	 * @var ilObjIndividualAssessment
	 */
	protected $iass;

	/**
	 * @var ilAccessHandler
	 */
	protected $handler;

	/**
	 * @var ilRbacAdmin
	 */
	protected $admin;

	/**
	 * ilRbacReview
	 */
	protected $review;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var string[]
	 */
	protected $mass_global_permissions_cache;

	const DEFAULT_ROLE = 'il_iass_member';

	public function __construct(ilObjIndividualAssessment $iass, ilAccessHandler $handler, ilRbacAdmin $admin, ilRbacReview $review, ilObjUser $usr) {
		$this->iass = $iass;
		$this->handler = $handler;
		$this->admin = $admin;
		$this->review = $review;
		$this->usr = $usr;
		$this->mass_global_permissions_cache = array();
	}

	/**
	 * @inheritdoc
	 */
	public function checkAccessToObj($operation) {
		if ($operation == "read_learning_progress") {
			return $this->handler->checkRbacOrPositionPermissionAccess("read_learning_progress", "read_learning_progress", $this->iass->getRefId());
		}
		if ($operation == "edit_learning_progress") {
			return $this->handler->checkRbacOrPositionPermissionAccess("edit_learning_progress", "write_learning_progress", $this->iass->getRefId());
		}

		return $this->handler->checkAccessOfUser($this->usr->getId(), $operation, '', $this->iass->getRefId(), 'iass');
	}

	/**
	 * @inheritdoc
	 */
	public function initDefaultRolesForObject(ilObjIndividualAssessment $iass) {
		$role = ilObjRole::createDefaultRole(
				$this->getRoleTitleByObj($iass),
				"Admin of iass obj_no.".$iass->getId(),
				self::DEFAULT_ROLE,
				$iass->getRefId()
		);
	}

	/**
	 * @inheritdoc
	 */
	public function assignUserToMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass) {
		return $this->admin->assignUser($this->getMemberRoleIdForObj($iass),$usr->getId());
	}

	/**
	 * @inheritdoc
	 */
	public function deassignUserFromMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass) {
		return $this->admin->deassignUser($this->getMemberRoleIdForObj($iass),$usr->getId());
	}

	protected function getRoleTitleByObj(ilObjIndividualAssessment $iass) {
		return self::DEFAULT_ROLE.'_'.$iass->getRefId();
	}

	protected function getMemberRoleIdForObj(ilObjIndividualAssessment $iass) {
		return current($this->review->getLocalRoles($iass->getRefId()));
	}

	/**
	 * User view iass object
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayViewObject($use_cache = true)
	{
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('read');
		}

		return $this->checkAccessToObj('read');
	}

	/**
	 * User edit iass
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayEditObject($use_cache = true)
	{
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('write');
		}

		return $this->checkAccessToObj('write');
	}

	/**
	 * User edit permissions
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayEditPermissions($use_cache = true)
	{
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('edit_permission');
		}

		return $this->checkAccessToObj('edit_permission');
	}

	/**
	 * User may edit members
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayEditMembers($use_cache = true)
	{
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('edit_members');
		}

		return $this->checkAccessToObj('edit_members');
	}

	/**
	 * User may view gradings
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayViewUser($use_cache = true)
	{
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('read_learning_progress');
		}

		return $this->checkAccessToObj('read_learning_progress');
	}

	/**
	 * User may grade
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayGradeUser($use_cache = true)
	{
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('edit_learning_progress');
		}

		return $this->checkAccessToObj('edit_learning_progress');
	}

	/**
	 * User may grade
	 *
	 * @param  int	$a_user_id
	 *
	 * @return bool
	 */
	public function mayGradeUserById($a_user_id)
	{
		return count($this->handler->filterUserIdsByRbacOrPositionOfCurrentUser("edit_learning_progress", "set_lp", $this->iass->getRefId(), [$a_user_id])) > 0;
	}

	/**
	 * User may Amend grading
	 *
	 * @param bool 	$use_cache
	 *
	 * @return bool
	 */
	public function mayAmendGradeUser($use_cache = true) {
		if ($use_cache) {
			return $this->cacheCheckAccessToObj('amend_grading');
		}

		return $this->checkAccessToObj('amend_grading');
	}

	/**
	 * Get permission state from cache
	 *
	 * @param string 	$operation
	 *
	 * @return bool
	 */
	protected function cacheCheckAccessToObj($operation)
	{
		$iass_id = $this->iass->getId();
		$user_id = $this->usr->getId();

		if (!isset($this->mass_global_permissions_cache[$iass_id][$user_id][$operation])) {
			$this->mass_global_permissions_cache[$iass_id][$user_id][$operation]
				= $this->checkAccessToObj($operation);
		}

		return $this->mass_global_permissions_cache[$iass_id][$user_id][$operation];
	}
}
