<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

class ValueToTaskContainer extends \ActiveRecord {

	public static function returnDbTableName() {
		return "il_bt_value_to_task";
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
	 * @con_fieldtype  integer
	 * @con_has_field  true
	 * @con_length     8
	 */
	protected $task_id;
	/**
	 * @var int
	 *
	 * @con_fieldtype  integer
	 * @con_has_field  true
	 * @con_length     8
	 */
	protected $value_id;
	/**
	 * @var int
	 *
	 * @con_fieldtype  integer
	 * @con_has_field  true
	 * @con_length     8
	 */
	protected $bucket_id;


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
	public function getTaskId(): int {
		return $this->task_id;
	}


	/**
	 * @param int $task_id
	 */
	public function setTaskId(int $task_id) {
		$this->task_id = $task_id;
	}


	/**
	 * @return int
	 */
	public function getValueId(): int {
		return $this->value_id;
	}


	/**
	 * @param int $value_id
	 */
	public function setValueId(int $value_id) {
		$this->value_id = $value_id;
	}


	/**
	 * @return int
	 */
	public function getBucketId(): int {
		return $this->bucket_id;
	}


	/**
	 * @param int $bucket_id
	 */
	public function setBucketId(int $bucket_id) {
		$this->bucket_id = $bucket_id;
	}
}
