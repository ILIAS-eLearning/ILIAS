<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Aggregate;

class CourseMember {

	/**
	 * @var Course
	 */
	private $course;
	/**
	 * @var int
	 */
	private $usr_id;


	/**
	 * CourseMember constructor.
	 *
	 * @param Course $course
	 * @param int    $usr_id
	 */
	public function __construct(Course $course, int $usr_id) {
		$this->course = $course;
		$this->usr_id = $usr_id;
	}


	public function usr_id() {
		return $this->usr_id;
	}


	public function course() {
		return $this->course;
	}
}