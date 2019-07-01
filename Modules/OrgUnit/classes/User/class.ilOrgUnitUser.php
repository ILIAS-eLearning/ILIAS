<?php

namespace OrgUnit\User;

use OrgUnit\Positions\ilOrgUnitPosition;

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
	protected $org_unit_positions = [];
	/**
	 * @var ilOrgUnitUser[]
	 */
	protected $superiors = [];


	/**
	 * @param int $user_id
	 *
	 * @return ilOrgUnitUser
	 */
	public static function getInstanceById(int $user_id): self {

		if (null === static::$instances[$user_id]) {
			$org_unit_user_repository = new ilOrgUnitUserRepository();
			static::$instances[$user_id] = $org_unit_user_repository->getOrgUnitUser($user_id);
		}

		return static::$instances[$user_id];
	}


	/**
	 * @param int    $user_id
	 * @param string $login
	 * @param string $email
	 *
	 * @return ilOrgUnitUser
	 */
	public static function getInstance(int $user_id, string $login, string $email): self {
		if (null === static::$instances[$user_id]) {
			static::$instances[$user_id] = new static($user_id, $login, $email);
		}

		return static::$instances[$user_id];
	}


	private function __construct(int $user_id, string $login, string $email) {
		$this->user_id = $user_id;
		$this->login = $login;
		$this->email = $email;
	}


	/**
	 * @param ilOrgUnitUser $org_unit_user
	 */
	public function addSuperior($org_unit_user) {
		$this->superiors[] = $org_unit_user;
	}


	/**
	 * @param ilOrgUnitPosition $org_unit_position
	 */
	public function addPositions($org_unit_position) {
		$this->org_unit_positions[] = $org_unit_position;
	}


	/**
	 * @return ilOrgUnitUser[]
	 *
	 * eager loading
	 * @var array ilOrgUnitUser
	 */
	public function getSuperiors() {

		if (count($this->superiors) == 0) {
			$this->loadSuperiors();
		}

		return $this->superiors;
	}


	public function loadSuperiors() {
		$org_unit_user_repository = new ilOrgUnitUserRepository();
		$org_unit_user_repository->loadSuperiors([ $this->user_id ]);
	}


	/**
	 * @return ilOrgUnitPosition[]
	 *
	 * eager loading
	 */
	public function getOrgUnitPositions(): array {


		if (count($this->org_unit_positions) == 0) {
			$this->loadOrgUnitPositions();
		}

		return $this->org_unit_positions;
	}


	/**
	 * @return ilOrgUnitPosition[]
	 *
	 * eager loading
	 */
	protected function loadOrgUnitPositions(): array {
		$org_unit_user_repository = new ilOrgUnitUserRepository();
		$org_unit_user_repository->loadPositions([ $this->user_id ]);
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