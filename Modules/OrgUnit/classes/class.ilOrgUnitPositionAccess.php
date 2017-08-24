<?php

/**
 * Class ilOrgUnitPositionAccess
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionAccess implements ilOrgUnitPositionAccessHandler, ilOrgUnitPositionAndRBACAccessHandler {

	/**
	 * @var \ilOrgUnitUserAssignmentQueries
	 */
	protected $ua;
	/**
	 * @var \ilOrgUnitGlobalSettings
	 */
	protected $settings;
	/**
	 * @var array
	 */
	protected static $ref_id_obj_type_map = array();


	/**
	 * ilOrgUnitPositionAccess constructor.
	 */
	public function __construct() {
		$this->settings = ilOrgUnitGlobalSettings::getInstance();
		$this->ua = ilOrgUnitUserAssignmentQueries::getInstance();
	}


	/**
	 * @param int[] $user_ids List of ILIAS-User-IDs which shall be filtered
	 *
	 * @return int[] Filtered List of ILIAS-User-IDs
	 */
	public function filterUserIdsForCurrentUsersPositionsAndAnyPermission(array $user_ids) {
		$current_user_id = $this->getCurrentUsersId();

		return $this->filterUserIdsForUsersPositionsAndAnyPermission($current_user_id, $user_ids);
	}


	/**
	 * @param int[] $user_ids    List of ILIAS-User-IDs which shall be filtered
	 *
	 * @param int   $for_user_id ID od the user, for which
	 *
	 * @return int[] Filtered List of ILIAS-User-IDs
	 */
	public function filterUserIdsForUsersPositionsAndAnyPermission(array $user_ids, $for_user_id) {
		// TODO: Implement filterUserIdsForUsersPositionsAndAnyPermission() method.
		throw new ilException('Not yet implemented!');
	}


	/**
	 * @param int[]  $user_ids List of ILIAS-User-IDs which shall be filtered
	 *
	 * @param string $permission
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @throws \ilOrgUnitAccessException when a unknown permission is used. See the list of
	 *                                   available permissions in interface
	 *                                   ilOrgUnitPositionAccessHandler
	 *
	 *
	 * @return int[] Filtered List of ILIAS-User-IDs
	 */
	public function filterUserIdsForCurrentUsersPositionsAndPermission(array $user_ids, $permission) {
		$current_user_id = $this->getCurrentUsersId();

		return $this->filterUserIdsForUsersPositionsAndPermission($user_ids, $current_user_id, $permission);
	}


	/**
	 * @param int[]  $user_ids List of ILIAS-User-IDs which shall be filtered
	 * @param int    $for_user_id
	 * @param string $permission
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @throws \ilOrgUnitAccessException when a unknown permission is used. See the list of
	 *                                   available permissions in interface
	 *                                   ilOrgUnitPositionAccessHandler
	 *
	 * @return int[] Filtered List of ILIAS-User-IDs
	 */
	public function filterUserIdsForUsersPositionsAndPermission(array $user_ids, $for_user_id, $permission) {
		// FSX TODO no permission is checked
		$assignment_of_user = $this->ua->getAssignmentsOfUserId($for_user_id);
		$other_users_in_same_org_units = [];
		foreach ($assignment_of_user as $assignment) {
			$other_users_in_same_org_units = $other_users_in_same_org_units
			                                 + $this->ua->getUserIdsOfOrgUnit($assignment->getOrguId());
		}

		return array_intersect($user_ids, $other_users_in_same_org_units);
	}


	/**
	 * @param string $permission
	 * @param int[]  $on_user_ids List of ILIAS-User-IDs
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @return bool
	 */
	public function isCurrentUserBasedOnPositionsAllowedTo($permission, array $on_user_ids) {
		$current_user_id = $this->getCurrentUsersId();

		return $this->isUserBasedOnPositionsAllowedTo($current_user_id, $permission, $on_user_ids);
	}


	/**
	 * @param int    $which_user_id Permission check for this ILIAS-User-ID
	 * @param string $permission
	 * @param int[]  $on_user_ids   List of ILIAS-User-IDs
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @return bool
	 */
	public function isUserBasedOnPositionsAllowedTo($which_user_id, $permission, array $on_user_ids) {
		throw new ilException('Not yet implemented!');
		// TODO: Implement isUserBasedOnPositionsAllowedTo() method.
	}


	/**
	 * @param string $pos_perm
	 * @param int    $ref_id Reference-ID of the desired Object in the tree
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @return bool
	 */
	public function checkPositionAccess($pos_perm, $ref_id) {
		// If context is not activated, return same array of $user_ids
		$context = $this->getTypeForRefId($ref_id);
		if (!$this->settings->getObjectPositionSettingsByType($context)) {
			return false;
		}
		throw new ilException('Not yet implemented!');
	}


	/**
	 * @param string $pos_perm
	 * @param int    $ref_id
	 * @param int[]  $user_ids
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @return int[]
	 */
	public function filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, array $user_ids) {
		// If context is not activated, return same array of $user_ids
		if (!$this->settings->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))) {
			return $user_ids;
		}

		$current_user_id = $this->getCurrentUsersId();

		return $this->filterUserIdsByPositionOfUser($current_user_id, $pos_perm, $ref_id, $user_ids);
	}


	/**
	 * @param int    $user_id
	 * @param string $pos_perm
	 * @param int    $ref_id
	 * @param int[]  $user_ids
	 *
	 * @see getAvailablePositionRelatedPermissions for available permissions
	 *
	 * @return int[]
	 */
	public function filterUserIdsByPositionOfUser($user_id, $pos_perm, $ref_id, array $user_ids) {
		// If context is not activated, return same array of $user_ids
		if (!$this->settings->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))->isActive()) {
			return $user_ids;
		}

		// $all_available_users = $this->ua->getUserIdsOfOrgUnit()
		$operation = ilOrgUnitOperationQueries::findByOperationString($pos_perm);

		$allowed_user_ids = [];
		foreach ($this->ua->getPositionsOfUserId($user_id) as $position) {
			$permissions = ilOrgUnitPermissionQueries::getSetForRefId($ref_id, $position->getId());
			if (!$permissions->isOperationIdSelected($operation->getOperationId())) {
				continue;
			}

			foreach ($position->getAuthorities() as $authority) {
				switch ($authority->getOver()) {
					case ilOrgUnitAuthority::OVER_EVERYONE:
						switch ($authority->getScope()) {
							case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
								$allowed = $this->ua->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id);
								$allowed_user_ids = $allowed_user_ids + $allowed;
								break;
							case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
								throw new ilException('not yet implemented');
								break;
						}
						break;
					default:
						switch ($authority->getScope()) {
							case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
								$allowed = $this->ua->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver());
								$allowed_user_ids = $allowed_user_ids + $allowed;
								break;
							case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
								throw new ilException('not yet implemented');
								break;
						}
						break;
				}
			}
		}

		return array_intersect($user_ids, $allowed_user_ids);
	}


	/**
	 * @return string[] array of available permissions used for position-related checks
	 */
	public function getAvailablePositionRelatedPermissions() {
		return ilOrgUnitOperation::getArray('operation_id', 'operation_string');
	}


	/**
	 * @param string $rbac_perm
	 * @param string $pos_perm           See the list of
	 *                                   available permissions in interface
	 *                                   ilOrgUnitPositionAccessHandler
	 * @param int    $ref_id             Reference-ID of the desired Object in the tree
	 *
	 * @return bool
	 */
	public function checkRbacOrPositionPermissionAccess($rbac_perm, $pos_perm, $ref_id) {
		// If context is not activated, return false
		if (!$this->settings->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))) {
			return false;
		}

		throw new ilException('Not yet implemented!');

		return false;
	}


	/**
	 * @param string $rbac_perm
	 * @param string $pos_perm           See the list of
	 *                                   available permissions in interface
	 *                                   ilOrgUnitPositionAccessHandler
	 * @param int    $ref_id             Reference-ID of the desired Object in the tree
	 * @param int[]  $user_ids
	 *
	 * @return int[]
	 */
	public function filterUserIdsByRbacOrPositionOfCurrentUser($rbac_perm, $pos_perm, $ref_id, array $user_ids) {
		// If context is not activated, return same array of $user_ids
		if (!$this->settings->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))) {
			return $user_ids;
		}

		throw new ilException('Not yet implemented!');

		return $user_ids;
	}


	//
	// Helpers
	//

	/**
	 * @return \ILIAS\DI\Container
	 */
	private function dic() {
		return $GLOBALS['DIC'];
	}


	/**
	 * @return int
	 */
	private function getCurrentUsersId() {
		return $this->dic()->user()->getId();
	}


	/**
	 * @param $ref_id
	 *
	 * @return mixed
	 */
	private function getTypeForRefId($ref_id) {
		if (!isset(self::$ref_id_obj_type_map[$ref_id])) {
			self::$ref_id_obj_type_map[$ref_id] = ilObject2::_lookupType($ref_id, true);
		}

		return self::$ref_id_obj_type_map[$ref_id];
	}
}
