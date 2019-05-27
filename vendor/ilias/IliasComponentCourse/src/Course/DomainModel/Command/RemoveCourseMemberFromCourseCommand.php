<?php

namespace srag\IliasComponentCourse\Course\Command;

use srag\IliasComponent\Context\Command\Command\Command;

/**
 * Class RemoveCourseMemberFromCourseCommand
 *
 * @package srag\IliasComponentCourse\Course\Command\Command;
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class RemoveCourseMemberFromCourseCommand implements Command {

	/**
	 * @var int
	 */
	protected $obj_id;
	/**
	 * @var int
	 */
	protected $user_id;


	/**
	 * RemoveCourseMemberFromCourseCommand constructor
	 *
	 * @param int $obj_id
	 * @param int $user_id
	 */
	public function __construct(int $obj_id, int $user_id) {
		$this->obj_id = $obj_id;
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->obj_id;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}


	/**
	 * @inheritdoc
	 */
	public static function messageName(): string {
		return self::class;
	}
}
