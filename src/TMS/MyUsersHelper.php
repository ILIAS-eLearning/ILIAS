<?php

namespace ILIAS\TMS;

trait MyUsersHelper {
	/**
	 * Get user ids and fullname of user, where current ilUser is allowed to book for
	 *
	 * @param int 	$superior_user_id
	 *
	 * @return string[]
	 */
	public function getUserWhereCurrentCanBookFor($superior_user_id) {
		require_once("Services/User/classes/class.ilObjUser.php");
		$ret = array();
		$employees = $this->getEmployesUnderUser($superior_user_id);
		$superiors = $this->getSuperiorsUnderUser($superior_user_id);
		$current = array((string)$superior_user_id);
		$members = array_unique(array_merge($current, $employees, $superiors));

		foreach($members as $user_id) {
			$ret[$user_id] = \ilObjUser::_lookupFullname($user_id);
		}

		uasort($ret, function($a, $b) {
			return strcmp($a, $b);
		});

		return $ret;
	}

	/**
	 * Get user ids of of user, where current ilUser is allowed to see bookings
	 *
	 * @param int 	$superior_user_id
	 *
	 * @return int[]
	 */
	public function getUsersWhereCurrentCanViewBookings($superior_user_id) {
		require_once("Services/User/classes/class.ilObjUser.php");
		$ret = array();
		$employees = $this->getEmployesUnderUser($superior_user_id);
		$superiors = $this->getSuperiorsUnderUser($superior_user_id);
		$members = array_filter(
						array_unique(
							array_merge($employees, $superiors)
						),
						function($user_id) use ($superior_user_id) {
							return $user_id != $superior_user_id;
						}
				);

		foreach($members as $user_id) {
			$ret[$user_id] = \ilObjUser::_lookupFullname($user_id);
		}

		uasort($ret, function($a, $b) {
			return strcmp($a, $b);
		});

		return $ret;
	}

	/**
	 * Returns employees under current user
	 *
	 * @param int 	$superior_user_id
	 *
	 * @return int[]
	 */
	protected function getEmployesUnderUser($superior_user_id) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		return \ilObjOrgUnitTree::_getInstance()->getEmployeesUnderUser($superior_user_id, true);
	}

	/**
	 * Returns superiors under current user
	 *
	 * @param int 	$superior_user_id
	 *
	 * @return int[]
	 */
	protected function getSuperiorsUnderUser($superior_user_id) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		return \ilObjOrgUnitTree::_getInstance()->getSuperiorsUnderUser($superior_user_id, true);
	}
}