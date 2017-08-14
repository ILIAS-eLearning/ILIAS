<?php

/**
 * Class ilOrgUnitPositionAccessHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilOrgUnitPositionAccessHandler {

	/**
	 * @param int[] $user_ids
	 *
	 * @return int[]
	 */
	public function filterUserIdsForCurrentUsersPositions(array $user_ids);


	/**
	 * @param int[]    $user_ids
	 *
	 * @param      int $for_user_id ID od the user, for which
	 *
	 * @return int[]
	 */
	public function filterUserIdsForUsersPositions(array $user_ids, $for_user_id);
}
