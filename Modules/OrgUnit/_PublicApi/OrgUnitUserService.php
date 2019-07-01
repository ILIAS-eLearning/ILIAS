<?php
namespace OrgUnit\_PublicApi;
use OrgUnit\User\ilOrgUnitUser;
use OrgUnit\User\ilOrgUnitUserRepository;


class OrgUnitUserService {

	public function __construct() {

	}


	/**
	 * @param array $user_ids
	 * @param bool  $with_superios
	 * @param bool  $with_positions
	 *
	 * @return ilOrgUnitUser[]
	 */
	public function getUsers(array $user_ids, $with_superios = false, $with_positions = false) {
		$org_unit_user_repository = new ilOrgUnitUserRepository();

		if($with_superios) {
			$org_unit_user_repository->withSuperiors();
		}
		if($with_positions) {
			$org_unit_user_repository->withPositions();
		}

		return $org_unit_user_repository->getOrgUnitUsers($user_ids);
	}

	public function getEnailAdressesOfSuperiors(array $user_ids):array {
		$org_unit_user_repository = new ilOrgUnitUserRepository();
		$org_unit_user_repository->withSuperiors();

		return $org_unit_user_repository->getEmailAdressesOfSuperiors($user_ids);
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