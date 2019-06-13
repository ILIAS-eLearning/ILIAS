<?php
namespace OrgUnit\interfaces;

interface ilOrgUnitUserRepositoryInterface {

	/**
	 * @param array $user_ids
	 *
	 * @return array $users
	 */
	public function findAllUsersByUserIds($user_ids);

}