<?php

namespace OrgUnit\User;
use OrgUnit\Positions\ilOrgUnitPosition;
use OrgUnit\Positions\UserAssignment\ilOrgUnitUserAssignmentRepository;
use OrgUnit\_PublicApi\OrgUnitUserSpecification;

class ilOrgUnitUser {

	/**
	 * @var self[]
	 */
	protected static $instances;
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var string
	 */
	protected $login;
	/**
	 * @var string
	 */
	protected $email;
	/**
	 * @var ilOrgUnitPosition[]
	 */
	protected $org_unit_positions;
	/**
	 * @var ilOrgUnitUser[]
	 */
	protected $superiors;
	/**
	 * @var OrgUnitUserSpecification
	 */
	protected $orgu_user_spec;


	/**
	 * @param int                               $user_id
	 * @param string                            $login
	 * @param string                            $email
	 * @param OrgUnitUserSpecification $orgu_user_spec
	 *
	 * @return ilOrgUnitUser
	 */
	public static function getInstance(int $user_id, string $login, string $email, OrgUnitUserSpecification $orgu_user_spec): self {

		if (null === static::$instances[$user_id]) {
			static::$instances[$user_id] = new static($user_id, $login, $email, $orgu_user_spec);
		}

		return static::$instances[$user_id];
	}


	public function __construct(int $user_id, string $login, string $email, $orgu_user_spec) {
		$this->user_id = $user_id;
		$this->login = $login;
		$this->email = $email;
		$this->orgu_user_spec = $orgu_user_spec;
	}


	/**
	 * @return ilOrgUnitUser[]
	 *
	 * eager loading
	 * @var array ilOrgUnitUser
	 */
	public function getSuperiors() {

		if ($this->orgu_user_spec->areCorrespondingSuperiorsLoaded() === true) {
			return $this->superiors;
		}

		return $this->loadSuperiors();
	}

	/**
	 * @return ilOrgUnitUser[]
	 *
	 * eager loading
	 * @var array ilOrgUnitUser
	 */
	public function loadSuperiors() {
		$user_assignment_repository = new ilOrgUnitUserAssignmentRepository();
		$empl_superior = $user_assignment_repository->getEmplSuperiorList($this->orgu_user_spec->getUserIdsToConsider());

		$arr_sup = [];
		foreach($empl_superior as $empl => $sup) {
			foreach($sup as $user_id) {
				$arr_sup[] = $user_id;
			}
		}
		$spec = new OrgUnitUserSpecification($arr_sup);
		$rep = ilOrgUnitUserRepository::getInstance($spec);


		$this->orgu_user_spec->setCorrespondingSuperiorsLoaded(true);

		$this->superiors =  $rep->findAllUsersByUserIds($arr_sup);

		return $this->superiors;
	}


	/**
	 * @return ilOrgUnitPosition[]
	 *
	 * eager loading
	 */
	public function getOrgUnitPositions(): array {

		if ($this->orgu_user_spec->areAssignedPositionsLoaded() === true) {
			return $this->org_unit_positions;
		}

		return $this->loadOrgUnitPositions();


	}


	/**
	 * @return ilOrgUnitPosition[]
	 *
	 * eager loading
	 */
	protected function loadOrgUnitPositions(): array {

		$user_assignment_repository = new ilOrgUnitUserAssignmentRepository();
		$this->org_unit_positions = $user_assignment_repository->findAllUserAssingmentsByUserIds($this->orgu_user_spec->getUserIdsToConsider());

		return $this->org_unit_positions;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}


	/**
	 * @return string
	 */
	public function getLogin(): string {
		return $this->login;
	}


	/**
	 * @return string
	 */
	public function getEmail(): string {
		return $this->email;
	}
}