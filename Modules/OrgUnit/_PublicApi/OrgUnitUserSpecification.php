<?php
namespace OrgUnit\_PublicApi;

use \ilException;

class OrgUnitUserSpecification {

	/**
	 * @var array
	 */
	protected $user_ids_to_consider;
	/**
	 * @var bool
	 */
	protected $org_unit_user_loaded = false;
	/**
	 * @var bool
	 */
	protected $assigned_positions_loaded = false;
	/**
	 * @var bool
	 */
	protected $corresponding_superiors_loaded = false;


	/**
	 * OrgUnitUserSpecification constructor.
	 *
	 * @param int[] $user_ids          Array with user_ids for Filterung or an empty array
	 * @param bool  $check_permissions Delegate the Permission Check -
	 *                                 not implemented yet!
	 *
	 * @throws ilException
	 */
	public function __construct(array $user_ids, $check_permissions = false) {
		if ($check_permissions === true) {
			throw new ilException('Permission Check has to be done by ourself!');
		}

		$this->user_ids_to_consider = $user_ids;
	}


	/**
	 * @return array|int[]
	 */
	public function getUserIdsToConsider() {
		return $this->user_ids_to_consider;
	}


	public function setAssignedPositionsLoaded($bool) {
		$this->assigned_positions_loaded = $bool;
	}


	/**
	 * @return bool
	 */
	public function areAssignedPositionsLoaded(): bool {
		return $this->assigned_positions_loaded;
	}


	/**
	 * @return bool
	 */
	public function areCorrespondingSuperiorsLoaded(): bool {
		return $this->corresponding_superiors_loaded;
	}

	public function setCorrespondingSuperiorsLoaded($bool) {
		$this->corresponding_superiors_loaded = $bool;
	}
}