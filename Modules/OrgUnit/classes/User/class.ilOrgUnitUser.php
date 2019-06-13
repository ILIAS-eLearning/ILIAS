<?php

namespace OrgUnit\User\ilOrgUnitUser;
use OrgUnit\Positions\ilOrgUnitPosition;

class ilOrgUnitUser {

	/**
	 * @var self
	 */
	protected static $instance = 0;
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
	 * @var ilOrgUnitUserAssignmentRepository
	 */
	protected $user_assignment_repository;


	/**
	 * @param int                               $user_id
	 * @param string                            $login
	 * @param string                            $email
	 * @param ilOrgUnitUserAssignmentRepository $user_assignment_repository
	 *
	 * @return ilOrgUnitUser
	 */
	public static function getInstance(int $user_id, string $login, string $email, ilOrgUnitUserAssignmentRepository $user_assignment_repository): self {

		if (null === static::$instance) {
			static::$instance = new static($user_id, $login, $email, $user_assignment_repository);
		}

		return static::$instance;
	}


	public function __construct(int $user_id, string $login, string $email, $user_assignment_repository) {
		$this->user_id = $user_id;
		$this->login = $login;
		$this->email = $email;
		$this->user_assignment_repository = $user_assignment_repository;
	}


	/**
	 * @return ilOrgUnitUser[]
	 *
	 * eager loading
	 * @var $int [] $user_ids
	 */
	public function getSuperiors() {

		//The Instance which created this object here.
		$org_unit_repository = ilOrgUnitUserRepository::getInstance();
		$bag = $org_unit_repository->getBag();

		$empl_superior = $this->user_assignment_repository->getEmplSuperiorList($this->bag['iser_ids']);

		$bag['user_superior_loaded'] = true;

		return $empl_superior;
	}


	/**
	 * @return ilOrgUnitPosition[]
	 *
	 * eager loading
	 */
	public function getOrgUnitPositions(): array {

		//The Instance which created this object here.
		$org_unit_repository = ilOrgUnitUserRepository::getInstance();
		$bag = $org_unit_repository->getBag();

		if ($bag['user_assignment_loadad'] === true) {
			return $this->org_unit_positions;
		}

		return $this->user_assignment_repository->findAllUserAssingmentsByUserIds($bag['user_ids']);
	}


	/**
	 * @return ilOrgUnitPosition[]
	 *
	 * eager loading
	 */
	protected function loadOrgUnitPositions(): array {
		//The Instance which created this object here.
		$org_unit_repository = ilOrgUnitUserRepository::getInstance();
		$user_ids = $org_unit_repository->getBag();

		$this->org_unit_positions = $this->user_assignment_repository->findAllUserAssingmentsByUserIds($user_ids);

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