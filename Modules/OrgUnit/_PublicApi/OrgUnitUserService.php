<?php
namespace OrgUnit\_PublicApi;


class OrgUnitUserService {

	/**
	 * OrgUnitUserSpecification
	 */
	protected $org_unit_user_specification;

	/**
	 * OrgUnitUserFactory constructor.
	 *
	 * @param OrgUnitUserSpecification
	 */
	public function __construct($org_unit_user_specification) {
		$this->org_unit_user_specification = $org_unit_user_specification;
	}

	public function getUsers() {
		$org_unit_user_repository = ilOrgUnitUserRepository::getInstance( $this->org_unit_user_specification);
	}

	public function getOnlyUsersEmailAddress() {

	}

	public function getOnlySuperiorsOfUsers() {

	}

	public function getOnlySuperiorsOfUsersEmailAdress() {

	}

	public function getOnlyPositionOfUsers() {

	}
}