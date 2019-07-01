<?php

namespace OrgUnit\User;

use ilOrgUnitUserAssignment;
use OrgUnit\Positions\ilOrgUnitPosition;

/**
 * Class ilOrgUnitUserRepository
 *
 * @author: Martin Studer   <ms@studer-raimann.ch>
 */
class ilOrgUnitUserRepository {

	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var self[]
	 */
	protected static $instance;
	/**
	 * @var ilOrgUnitUser[]
	 */
	protected $orgu_users;
	/**
	 * @var bool
	 */
	protected $with_superiors = false;
	/**
	 * @var bool
	 */
	protected $with_positions = false;


	/**
	 * ilOrgUnitUserRepository constructor.
	 *
	 */
	public function __construct() {
		global $DIC;
		$this->dic = $DIC;
	}


	public function withSuperiors() {
		$this->with_superiors = true;

		return $this;
	}


	public function withPositions() {
		$this->with_positions = true;

		return $this;
	}


	/**
	 * @param array $user_ids
	 *
	 * @return ilOrgUnitUser[]
	 */
	public function getOrgUnitUsers(array $arr_user_id): array {

		$this->orgu_users = $this->loadUsersByUserIds($arr_user_id);

		if ($this->with_superiors === true) {
			$this->loadSuperiors($arr_user_id);
		}

		if ($this->with_positions === true) {
			$this->loadPositions($arr_user_id);
		}

		return $this->orgu_users;
	}


	/**
	 * @param array $user_ids
	 *
	 * @return ilOrgUnitUser|
	 */
	public function getOrgUnitUser($user_id): ?ilOrgUnitUser {

		$this->orgu_users = $this->loadUsersByUserIds([ $user_id ]);

		if (count($this->orgu_users) == 0) {
			return null;
		}

		if ($this->with_superiors === true) {
			$this->loadSuperiors([ $user_id ]);
		}

		return $this->orgu_users[0];
	}


	/**
	 * @return ilOrgUnitUser[]
	 *
	 * eager loading
	 * @var array ilOrgUnitUser
	 */
	public function loadSuperiors(array $user_ids): void {

		global $DIC;

		$sql = "SELECT 
				orgu_ua.orgu_id AS orgu_id,
				orgu_ua.user_id AS empl_usr_id,
				orgu_ua2.user_id as sup_usr_id,
				superior.email as sup_email,
				superior.login as sup_login
				FROM
				il_orgu_ua as orgu_ua,
				il_orgu_ua as orgu_ua2
				inner join usr_data as superior on superior.usr_id = orgu_ua2.user_id
				WHERE
				orgu_ua.orgu_id = orgu_ua2.orgu_id 
				and orgu_ua.user_id <> orgu_ua2.user_id 
				and orgu_ua.position_id = " . ilOrgUnitPosition::CORE_POSITION_EMPLOYEE . "
				and orgu_ua2.position_id = " . ilOrgUnitPosition::CORE_POSITION_SUPERIOR . " 
				AND " . $DIC->database()->in('orgu_ua.user_id', $user_ids, false, 'integer');

		$st = $DIC->database()->query($sql);

		$empl_id_sup_ids = [];
		while ($data = $DIC->database()->fetchAssoc($st)) {
			$org_unit_user = ilOrgUnitUser::getInstanceById($data['empl_usr_id']);
			$superior = ilOrgUnitUser::getInstance($data['sup_usr_id'], $data['sup_login'], $data['sup_email']);
			$org_unit_user->addSuperior($superior);
		}
	}


	/**
	 * @param $user_ids
	 *
	 * @return ilOrgUnitPosition[]
	 */
	public function loadPositions(array $user_ids): array {
		/**
		 * @var $assignment ilOrgUnitUserAssignment
		 */
		$positions = [];
		if (count(ilOrgUnitUserAssignment::where([ 'user_id' => $user_ids ])->get()) > 0) {
			foreach (ilOrgUnitUserAssignment::where([ 'user_id' => $user_ids ])->get() as $assignment) {
				$org_unit_user = ilOrgUnitUser::getInstanceById($assignment->getUserId());
				$org_unit_user->addPositions(ilOrgUnitPosition::find($assignment->getPositionId()));
			}
		}

		return $positions;
	}


	/**
	 * @param array $user_ids
	 *
	 * @return array ilOrgUnitUser
	 */
	private function loadUsersByUserIds($user_ids): array {
		$users = array();

		$q = "SELECT * FROM usr_data WHERE " . $this->dic->database()->in('usr_id', $user_ids, false, 'int');

		$set = $this->dic->database()->query($q);

		while ($row = $this->dic->database()->fetchAssoc($set)) {
			$users[] = ilOrgUnitUser::getInstance($row['usr_id'], $row['login'], $row['email']);
		}

		return $users;
	}
}