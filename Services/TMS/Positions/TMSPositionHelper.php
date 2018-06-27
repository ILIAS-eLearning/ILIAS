<?php

/**
 * Helper to get user ids via positions and auhtorites
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class TMSPositionHelper {
	/**
	 * @var ilOrgUnitUserAssignmentQueries
	 */
	protected $orgua_queries;

	public function __construct(ilOrgUnitUserAssignmentQueries $orgua_queries) {
		$this->orgua_queries = $orgua_queries;
	}

	/**
	 * Get all user ids where user has authorities
	 *
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	public function getUserIdWhereUserHasAuhtority($user_id) {
		$positons = $this->getPositionsOf($user_id);

		$user_ids = array();
		foreach ($positons as $positon) {
			$user_ids = array_merge($user_ids, $this->getUserIdsByPositionAndUser($positon, $user_id));
		}

		return array_unique($user_ids);
	}

	/**
	 * Get all orgu ids where use has any auhtority
	 *
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	public function getOrgUnitIdsWhereUserHasAuthority($user_id) {
		$positons = $this->getPositionsOf($user_id);
		$positons = array_filter($positons, function($p) {
			if(count($p->getAuthorities()) > 0) {
				return $p;
			}
		});

		$orgus = array();

		foreach($positons as $positon) {
			$orgus = array_merge(
				$orgus,
				$orgus = $this->orgua_queries->getOrgUnitIdsOfUsersPosition($positon->getId(), $user_id)
			);
		}

		return array_unique($orgus);
	}

	/**
	 * Get all orgu assignments of user
	 *
	 * @param int 	$user_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	protected function getAssignmentsOf($user_id) {
		return $this->orgua_queries->getAssignmentsOfUserId($user_id);
	}

	/**
	 * Get positions of user on his aussignments
	 *
	 * @param int 	$user_id
	 *
	 * @return ilOrgUnitPosition[]
	 */
	protected function getPositionsOf($user_id) {
		require_once("Modules/OrgUnit/classes/Positions/class.ilOrgUnitPosition.php");
		$assignments = $this->getAssignmentsOf($user_id);
		return array_map(function($a) {
			return new ilOrgUnitPosition($a->getPositionId());
		}, $assignments);
	}

	/**
	 * Get all user id via positions
	 *
	 * @param ilOrgUnitPosition 	$position
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	protected function getUserIdsByPositionAndUser(ilOrgUnitPosition $position, $user_id) {
		require_once("Modules/OrgUnit/classes/Positions/Authorities/class.ilOrgUnitAuthority.php");
		$ids = array();
		foreach ($position->getAuthorities() as $authority) {
			switch ($authority->getOver()) {
				case ilOrgUnitAuthority::OVER_EVERYONE:
					switch ($authority->getScope()) {
						case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id)
							);
							break;
						case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id, true)
							);
							break;
					}
					break;
				default:
					switch ($authority->getScope()) {
						case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver())
							);
							break;
						case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver(), true)
							);
							break;
					}
			}
		}

		return $ids;
	}
}