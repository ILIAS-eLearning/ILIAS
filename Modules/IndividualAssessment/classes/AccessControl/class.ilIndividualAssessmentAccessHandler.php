<?php
require_once 'Modules/IndividualAssessment/interfaces/AccessControl/interface.IndividualAssessmentAccessHandler.php';
require_once 'Services/AccessControl/classes/class.ilObjRole.php';
/**
 * @inheritdoc
 * Deal with ilias rbac-system
 */
class ilIndividualAssessmentAccessHandler implements IndividualAssessmentAccessHandler {

	protected $handler;
	protected $admin;
	protected $review;

	const DEFAULT_ROLE = 'il_iass_member';

	public function __construct(ilAccessHandler $handler, ilRbacAdmin $admin, ilRbacReview $review, ilObjUser $usr) {
		$this->handler = $handler;
		$this->admin = $admin;
		$this->review = $review;
		$this->usr = $usr;
	}

	/**
	 * Can the current ilias user perform an operation on some Individual assessment? 
	 *
	 * @param	ilObjIndividualAssessment	$iass
	 * @param	string	$operation
	 * @return bool
	 */
	public function checkAccessToObj(ilObjIndividualAssessment $iass, $operation) {
		return $this->checkAccessOfUserToObj($this->usr,$iass,$operation);
	}

	/**
	 * @inheritdoc
	 */
	public function checkAccessOfUserToObj(ilObjUser $usr, ilObjIndividualAssessment $iass, $operation) {

		return $this->handler->checkAccessOfUser($usr->getId(), $operation, '', $iass->getRefId(), 'iass');
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
}