<?php

namespace OrgUnit\User;

use OrgUnit\Positions;

use OrgUnit\Positions\ilOrgUnitPosition;
use OrgUnit\_PublicApi\OrgUnitUserSpecification;

use OrgUnit\Positions\UserAssignment\ilOrgUnitUserAssignmentRepository;
use \ilOrgUnitUserRepositoryInterface;


/**
 * Class ilOrgUnitUserRepository
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilOrgUnitUserRepository implements ilOrgUnitUserRepositoryInterface {

	/**
	 * @var OrgUnitUserSpecification
	 */
	protected $org_unit_user_specification;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var self[]
	 */
	protected static $instance;


	/**
	 * @param OrgUnitUserSpecification $org_unit_user_specification
	 *
	 * @return ilOrgUnitUserRepository
	 */
	public static function getInstance(OrgUnitUserSpecification $org_unit_user_specification): self {
		if (null === static::$instance[serialize($org_unit_user_specification->getUserIdsToConsider())]) {
			static::$instance[serialize($org_unit_user_specification->getUserIdsToConsider())] = new static($org_unit_user_specification);
		}

		return static::$instance[serialize($org_unit_user_specification->getUserIdsToConsider())];
	}


	/**
	 * ilOrgUnitUserRepository constructor.
	 */
	private function __construct($org_unit_user_specification) {
		global $DIC;
		$this->dic = $DIC;
		$this->org_unit_user_specification = $org_unit_user_specification;
	}


	/**
	 * @param array $user_ids
	 *
	 * @return array ilOrgUnitUser
	 */
	public function findAllUsersByUserIds($user_ids): array {
		$users = array();


		$q = "SELECT * FROM usr_data WHERE " . $this->dic->database()->in('usr_id', $user_ids, false, 'int');

		$set = $this->dic->database()->query($q);

		while ($row = $this->dic->database()->fetchAssoc($set)) {
			$users[] = new ilOrgUnitUser($row['usr_id'],
				$row['login'],
				$row['email'],
				$this->org_unit_user_specification
			);
		}
		return $users;
	}
}