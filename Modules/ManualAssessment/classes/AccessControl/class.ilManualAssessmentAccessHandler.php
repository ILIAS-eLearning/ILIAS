<?php
require_once 'Modules/ManualAssessment/interfaces/AccessControl/interface.ManualAssessmentAccessHandler.php';
require_once 'Services/AccessControl/classes/class.ilObjRole.php';
/**
 * @inheritdoc
 * Deal with ilias rbac-system
 */
class ilManualAssessmentAccessHandler implements ManualAssessmentAccessHandler {

	protected $handler;
	protected $admin;
	protected $review;

	const DEFAULT_ROLE = 'il_mass_member';

	public function __construct(ilAccessHandler $handler, ilRbacAdmin $admin, ilRbacReview $review, ilObjUser $usr) {
		$this->handler = $handler;
		$this->admin = $admin;
		$this->review = $review;
		$this->usr = $usr;
	}

	/**
	 * Can the current ilias user perform an operation on some manual assessment? 
	 *
	 * @param	ilObjManualAssessment	$mass
	 * @param	string	$operation
	 * @return bool
	 */
	public function checkAccessToObj(ilObjManualAssessment $mass, $operation) {
		return $this->checkAccessOfUserToObj($this->usr,$mass,$operation);
	}

	/**
	 * @inheritdoc
	 */
	public function checkAccessOfUserToObj(ilObjUser $usr, ilObjManualAssessment $mass, $operation) {

		return $this->handler->checkAccessOfUser($usr->getId(), $operation, '', $mass->getRefId(), 'mass');
	}

	/**
	 * @inheritdoc
	 */
	public function initDefaultRolesForObject(ilObjManualAssessment $mass) {
		$role = ilObjRole::createDefaultRole(
				$this->getRoleTitleByObj($mass),
				"Admin of mass obj_no.".$mass->getId(),
				self::DEFAULT_ROLE,
				$mass->getRefId()
		);
	}

	/**
	 * @inheritdoc
	 */
	public function assignUserToMemberRole(ilObjUser $usr, ilObjManualAssessment $mass) {
		return $this->admin->assignUser($this->getMemberRoleIdForObj($mass),$usr->getId());
	}

	/**
	 * @inheritdoc
	 */
	public function deassignUserFromMemberRole(ilObjUser $usr, ilObjManualAssessment $mass) {
		return $this->admin->deassignUser($this->getMemberRoleIdForObj($mass),$usr->getId());
	}

	protected function getRoleTitleByObj(ilObjManualAssessment $mass) {
		return self::DEFAULT_ROLE.'_'.$mass->getRefId();
	}

	protected function getMemberRoleIdForObj(ilObjManualAssessment $mass) {
		return current($this->review->getLocalRoles($mass->getRefId()));
	}
}