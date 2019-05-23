<?php

namespace ILIAS\App\CoreApp\Course\Domain\Command;

class AddCourseMemberToCourseCommand {

	/**
	 * @var int
	 */
	private $course_id;
	/**
	 * @var int
	 */
	private $user_id;


	public function __construct(int $course_id, int $user_id) {
		$this->course_id = $course_id;
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->course_id;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}
}
