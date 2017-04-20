<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Observer;

class ObserverContainer extends \ActiveRecord {

	public static function returnDbTableName() {
		return "il_bt_observer";
	}

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_sequence   true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $userId;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $rootTaskId;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $currentTaskId;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     2
	 */
	protected $state;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $totalNumberOfTasks;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     2
	 */
	protected $percentage = 0;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->userId;
	}

	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId) {
		$this->userId = $userId;
	}

	/**
	 * @return int
	 */
	public function getRootTaskId(): int {
		return $this->rootTaskId;
	}

	/**
	 * @param int $rootTaskId
	 */
	public function setRootTaskId(int $rootTaskId) {
		$this->rootTaskId = $rootTaskId;
	}

	/**
	 * @return int
	 */
	public function getCurrentTaskId(): int {
		return $this->currentTaskId;
	}

	/**
	 * @param int $currentTaskId
	 */
	public function setCurrentTaskId(int $currentTaskId) {
		$this->currentTaskId = $currentTaskId;
	}

	/**
	 * @return int
	 */
	public function getState(): int {
		return $this->state;
	}

	/**
	 * @param int $state
	 */
	public function setState(int $state) {
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getTotalNumberOfTasks(): int {
		return $this->totalNumberOfTasks;
	}

	/**
	 * @param int $totalNumberOfTasks
	 */
	public function setTotalNumberOfTasks(int $totalNumberOfTasks) {
		$this->totalNumberOfTasks = $totalNumberOfTasks;
	}

	/**
	 * @return int
	 */
	public function getPercentage(): int {
		return $this->percentage;
	}

	/**
	 * @param int $percentage
	 */
	public function setPercentage(int $percentage) {
		$this->percentage = $percentage;
	}
}