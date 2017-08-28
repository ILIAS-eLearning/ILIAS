<?php

/**
 * Class ilOrgUnitUserAssignmentQueries
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitUserAssignmentQueries {

	/**
	 * @var \ilOrgUnitUserAssignmentQueries
	 */
	protected static $instance;


	/**
	 * @return \ilOrgUnitUserAssignmentQueries
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @param $user_id
	 *
	 * @return ilOrgUnitPosition[]
	 */
	public function getPositionsOfUserId($user_id) {
		/**
		 * @var $assignment ilOrgUnitUserAssignment
		 */
		$positions = [];
		foreach ($this->getAssignmentsOfUserId($user_id) as $assignment) {
			$positions[] = ilOrgUnitPosition::find($assignment->getPositionId());
		}

		return $positions;
	}


	/**
	 * @param $user_id
	 * @param $position_id
	 *
	 * @return \ActiveRecord
	 * @throws \ilException
	 */
	public function getAssignmentOrFail($user_id, $position_id) {
		$ua = ilOrgUnitUserAssignment::where([
			'user_id'     => $user_id,
			'position_id' => $position_id,
		])->first();
		if (!$ua) {
			throw new  ilException('UserAssignement not found');
		}

		return $ua;
	}


	public function filterUserIdsDueToAuthorities($user_id, array $user_ids) {

	}


	/**
	 * @param $user_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getAssignmentsOfUserId($user_id) {
		return ilOrgUnitUserAssignment::where([ 'user_id' => $user_id ])->get();
	}


	/**
	 * @param $orgunit_ref_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserIdsOfOrgUnit($orgunit_ref_id) {
		return ilOrgUnitUserAssignment::where([ 'orgu_id' => $orgunit_ref_id ])
		                              ->getArray(null, 'user_id');
	}


	/**
	 * @param $orgunit_ref_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserIdsOfOrgUnits(array $orgunit_ref_id) {
		return ilOrgUnitUserAssignment::where([ 'orgu_id' => $orgunit_ref_id ])
		                              ->getArray(null, 'user_id');
	}


	/**
	 * @param      $position_id
	 * @param      $user_id
	 *
	 * @param bool $recursive
	 *
	 * @return \ilOrgUnitUserAssignment[]
	 * @internal param $orgunit_ref_id
	 */
	public function getUserIdsOfOrgUnitsOfUsersPosition($position_id, $user_id, $recursive = false) {
		return ilOrgUnitUserAssignment::where([ 'orgu_id' => $this->getOrgUnitIdsOfUsersPosition($position_id, $user_id, $recursive) ])
		                              ->getArray(null, 'user_id');
	}


	/**
	 * @param array $orgu_ids
	 * @param       $position_id
	 *
	 * @return int[]
	 */
	public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, $position_id) {
		return ilOrgUnitUserAssignment::where([
			'orgu_ids'    => $orgu_ids,
			'position_id' => $position_id,
		])->getArray(null, 'user_id');
	}


	/**
	 * @param       $user_id
	 * @param       $users_position_id
	 * @param       $position_id
	 *
	 * @param bool  $recursive
	 *
	 * @return int[]
	 */
	public function getUserIdsOfUsersOrgUnitsInPosition($user_id, $users_position_id, $position_id, $recursive = false) {
		return ilOrgUnitUserAssignment::where([
			'orgu_id'     => $this->getOrgUnitIdsOfUsersPosition($users_position_id, $user_id, $recursive),
			'position_id' => $position_id,
		])->getArray(null, 'user_id');
	}


	/**
	 * @param      $position_id
	 * @param      $user_id
	 *
	 * @param bool $recursive
	 *
	 * @return int[]
	 */
	public function getOrgUnitIdsOfUsersPosition($position_id, $user_id, $recursive = false) {
		$orgu_ids = ilOrgUnitUserAssignment::where([
			'position_id' => $position_id,
			'user_id'     => $user_id,
		])->getArray(null, 'orgu_id');

		if (!$recursive) {
			return $orgu_ids;
		}

		$recursive_orgu_ids = [];
		$tree = ilObjOrgUnitTree::_getInstance();
		foreach ($orgu_ids as $orgu_id) {
			$recursive_orgu_ids = $recursive_orgu_ids + $tree->getAllChildren($orgu_id);
		}

		return $recursive_orgu_ids;
	}


	/**
	 * @param $position_id
	 *
	 * @return int[]
	 */
	public function getUserIdsOfPosition($position_id) {
		return ilOrgUnitUserAssignment::where([
			'position_id' => $position_id,
		])->getArray(null, 'user_id');
	}


	/**
	 * @param $position_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserAssignmentsOfPosition($position_id) {
		return ilOrgUnitUserAssignment::where([
			'position_id' => $position_id,
		])->get();
	}
}
