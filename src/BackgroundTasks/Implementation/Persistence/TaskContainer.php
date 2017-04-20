<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

class TaskContainer extends \ActiveRecord {
	public static function returnDbTableName() {
		return "il_bt_task";
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