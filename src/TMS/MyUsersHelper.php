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
		$members = $this->getMembersUserHasAuthorities($superior_user_id);
		$current = array((string)$superior_user_id);
		$members = array_unique(array_merge($current, $members));

		foreach($members as $user_id) {
			$name_infos = \ilObjUser::_lookupName($user_id);
			$ret[$user_id] = $name_infos["lastname"].", ".$name_infos["firstname"];
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
		$members = $this->getMembersUserHasAuthorities($superior_user_id);
		$members = array_filter(
			$members,
			function($user_id) use ($superior_user_id) {
				return $user_id != $superior_user_id;
			}
		);

		foreach($members as $user_id) {
			$name_infos = \ilObjUser::_lookupName($user_id);
			$ret[$user_id] = $name_infos["lastname"].", ".$name_infos["firstname"];
		}

		uasort($ret, function($a, $b) {
			return strcmp($a, $b);
		});

		return $ret;
	}

	/**
	 * Get all user ids where user has authorities
	 *
	 * @param int 	§user_id
	 *
	 * @return int[]
	 */
	protected function getMembersUserHasAuthorities($user_id) {
		require_once("Services/TMS/Positions/TMSPositionHelper.php");
		require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
		$tms_pos_helper = new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance());
		return $tms_pos_helper->getUserIdWhereUserHasAuhtority($user_id);
	}
}