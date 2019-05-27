<?php

namespace srag\Course\Command\Aggregate\Course;

use srag\IliasComponent\Context\Aggregate\ValueObject;

/**
 * Class CourseMember
 *
 * @package srag\IliasComponentCourse\Course\Course\Query\Entity
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CourseMember implements ValueObject {

	/**
	 * @var int
	 */
	private $course_id;
	/**
	 * @var int
	 */
	private $usr_id;
	/**
	 * @var bool
	 */
	private $blocked;
	/**
	 * @var bool
	 */
	private $passed;
	/**
	 * @var int|null
	 */
	private $origin;
	/**
	 * @var int|null
	 */
	private $originTs;


	/**
	 * CourseMember constructor.
	 *
	 * @param int      $course_id
	 * @param int      $usr_id
	 * @param bool     $blocked
	 * @param bool     $passed
	 * @param int|null $origin
	 * @param int|null $originTs
	 */
	public function __construct(int $course_id, int $usr_id, bool $blocked, bool $passed, int $origin, int $originTs) {
		$this->course_id = $course_id;
		$this->usr_id = $usr_id;
		$this->blocked = $blocked;
		$this->passed = $passed;
		$this->origin = $origin;
		$this->originTs = $originTs;
	}
}
