<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

class ValueContainer extends \ActiveRecord {
	public static function returnDbTableName() {
		return "il_bt_value";
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
	 * @con_length     1
	 */
	protected $hasParentTask;

	/**
	 * @var int
	 *
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $parentTask;

	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $hash;

	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $type;

	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $class_path;

	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $class_name;

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
	public function getHasParentTask(): int {
		return $this->hasParentTask;
	}

	/**
	 * @param int $hasParentTask
	 */
	public function setHasParentTask(int $hasParentTask) {
		$this->hasParentTask = $hasParentTask;
	}

	/**
	 * @return int
	 */
	public function getParentTask(): int {
		return $this->parentTask;
	}

	/**
	 * @param int $parentTask
	 */
	public function setParentTask(int $parentTask) {
		$this->parentTask = $parentTask;
	}

	/**
	 * @return string
	 */
	public function getHash(): string {
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash(string $hash) {
		$this->hash = $hash;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getClassPath(): string {
		return $this->class_path;
	}

	/**
	 * @param string $class_path
	 */
	public function setClassPath(string $class_path) {
		$this->class_path = $class_path;
	}

	/**
	 * @return string
	 */
	public function getClassName(): string {
		return $this->class_name;
	}

	/**
	 * @param string $class_name
	 */
	public function setClassName(string $class_name) {
		$this->class_name = $class_name;
	}
}