<?php



interface ilOrgUnitUserRepositoryInterface {

	/**
	 * @param array $user_ids
	 *
	 * @return array $users
	 */
	public function getUsers(array $user_ids);

}