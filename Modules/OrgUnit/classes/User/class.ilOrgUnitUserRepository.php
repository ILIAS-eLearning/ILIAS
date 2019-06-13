<?php
namespace ilOrgUnitUserRepository;
use OrgUnit\Positions;

use OrgUnit\Positions\ilOrgUnitPosition;
use OrgUnit\_PublicApi\OrgUnitUserSpecification;
use OrgUnit\Interfaces\ilOrgUnitUserRepositoryInterface;
use OrgUnit\UserilOrgUnitUser;

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
	 * @param OrgUnitUserSpecification $org_unit_user_specification
	 *
	 * @return ilOrgUnitUserRepository
	 */
	public static function getInstance(OrgUnitUserSpecification $org_unit_user_specification): self {
		if (null === static::$instance) {
			static::$instance = new static($org_unit_user_specification);
		}

		return static::$instance;
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

		$q = "SELECT * FROM usr_data WHERE usr_id = " . $this->dic->database()->quote($user_ids, "in");

		$usr_set = $this->dic->database()->query($q);

		foreach ($this->dic->database()->fetchAssoc($usr_set) as $arr_usr) {
			$users[] = ilOrgUnitUser::getInstance($arr_usr['usr_id'], $arr_usr['login'], $arr_usr['email'], ilOrgUnitUserAssignmentRepository::getInstance());
		}



		return $users;
	}
}