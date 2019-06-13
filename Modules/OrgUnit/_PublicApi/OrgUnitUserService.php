<?php
namespace OrgUnit\_PublicApi;
use OrgUnit\User\ilOrgUnitUserRepository;


class OrgUnitUserService {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var OrgUnitUserSpecification
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
		$org_unit_user_repository = ilOrgUnitUserRepository::getInstance($this->org_unit_user_specification);

		return $org_unit_user_repository->findAllUsersByUserIds($this->org_unit_user_specification->getUserIdsToConsider());
	}

	/*
	public function getOnlyUsersEmailAddress() {

	}

	public function getOnlySuperiorsOfUsers() {

	}

	public function getOnlySuperiorsOfUsersEmailAdress() {

	}

	public function getOnlyPositionOfUsers() {

	}*/
}