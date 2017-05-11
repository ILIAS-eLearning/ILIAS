<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Observer;

require_once("./Services/ActiveRecord/class.ActiveRecord.php");

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
	protected $user_id;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $root_task_id = 0;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $current_task_id = 0;

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
	protected $total_number_of_tasks;

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
	public function getId() {
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
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId(int $user_id) {
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getRootTaskid(): int {
		return $this->root_task_id;
	}

	/**
	 * @param int $root_task_id
	 */
	public function setRootTaskid(int $root_task_id) {
		$this->root_task_id = $root_task_id;
	}

	/**
	 * @return int
	 */
	public function getCurrentTaskid(): int {
		return $this->current_task_id;
	}

	/**
	 * @param int $current_task_id
	 */
	public function setCurrentTaskid(int $current_task_id) {
		$this->current_task_id = $current_task_id;
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
	public function getTotalNumberoftasks(): int {
		return $this->total_number_of_tasks;
	}

	/**
	 * @param int $total_number_of_tasks
	 */
	public function setTotalNumberoftasks(int $total_number_of_tasks) {
		$this->total_number_of_tasks = $total_number_of_tasks;
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