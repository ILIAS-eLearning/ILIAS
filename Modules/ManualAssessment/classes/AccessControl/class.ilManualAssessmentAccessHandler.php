<?php
require_once 'Modules/ManualAssessment/interfaces/AccessControl/interface.ManualAssessmentAccessHandler.php';
require_once 'Services/AccessControl/classes/class.ilObjRole.php';
class ilManualAssessmentAccessHandler implements ManualAssessmentAccessHandler {

	protected $handler;
	protected $admin;
	protected $review;

	const DEAFULT_ROLE = 'il_mass_member';

	public function __construct(ilAccessHandler $handler, ilRbacAdmin $admin, ilRbacReview $review, ilObjUser $usr) {
		$this->handler = $handler;
		$this->admin = $admin;
		$this->review = $review;
		$this->usr;
	}

	public function checkAccessToObj(ilObjManualAssessment $mass, $operation) {
		return $this->checkAccessOfUserToObj($this->usr,$mass,$operation);
	}

	public function checkAccessOfUserToObj(ilObjUser $usr, ilObjManualAssessment $mass, $operation) {
		return $this->handler->checkAccessOfUser($usr->getId(), $operation, '', $mass->getRefId(), 'mass');
	}

	public function initDefaultRolesForObject(ilObjManualAssessment $mass) {
		$role = ilObjRole::createDefaultRole(
				$this->getRoleTitleByObj($mass),
				"Admin of mass obj_no.".$mass->getId(),
				self::DEAFULT_ROLE,
				$mass->getRefId()
		);
	}

	public function assignUserToMemberRole(ilObjUser $usr, ilObjManualAssessment $mass) {
		return $this->admin->assignUser($this->getMemberRoleIdForObj($mass),$usr->getId());
	}

	public function deassignUserFromMemberRole(ilObjUser $usr, ilObjManualAssessment $mass) {
		return $this->admin->deassignUser($this->getMemberRoleIdForObj($mass),$usr->getId());
	}

	protected function getRoleTitleByObj(ilObjManualAssessment $mass) {
		return self::DEAFULT_ROLE.'_'.$mass->getRefId();
	}

	protected function getMemberRoleIdForObj(ilObjManualAssessment $mass) {
		return current($this->review->getLocalRoles($mass->getRefId()));
	}
}