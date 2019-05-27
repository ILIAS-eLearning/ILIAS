<?php

namespace srag\Course\Command\Aggregate\Course;
use srag\IliasComponent\Context\Aggregate\ValueObject;


/**
 * Class CourseAdmin
 *
 * @package srag\IliasComponentCourse\Course\Course\Query\Entity
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CourseAdmin implements ValueObject {

	/**
	 * @var int
	 */
	private $course_id = 0;
	/**
	 * @var int
	 */
	private $usr_id = 0;
	/**
	 * @var bool
	 */
	private $blocked = false;
	/**
	 * @var bool
	 */
	private $notification = false;
	/**
	 * @var bool
	 */
	private $passed = false;
	/**
	 * @var int|null
	 */
	private $origin = null;
	/**
	 * @var int|null
	 */
	private $originTs = null;
	/**
	 * @var bool
	 */
	private $contact = false;
	/**
	 * @var bool
	 */
	private $admin = false;
	/**
	 * @var bool
	 */
	private $tutor = false;
	/**
	 * @var bool
	 */
	private $member = false;


	/**
	 * CourseAdmin constructor.
	 *
	 * @param int      $course_id
	 * @param int      $usr_id
	 * @param bool     $blocked
	 * @param bool     $notification
	 * @param bool     $passed
	 * @param int|null $origin
	 * @param int|null $originTs
	 * @param bool     $contact
	 * @param bool     $admin
	 * @param bool     $tutor
	 * @param bool     $member
	 */
	public function __construct(int $course_id, int $usr_id, bool $blocked, bool $notification, bool $passed, int $origin, int $originTs, bool $contact, bool $admin, bool $tutor, bool $member) {
		$this->course_id = $course_id;
		$this->usr_id = $usr_id;
		$this->blocked = $blocked;
		$this->notification = $notification;
		$this->passed = $passed;
		$this->origin = $origin;
		$this->originTs = $originTs;
		$this->contact = $contact;
		$this->admin = $admin;
		$this->tutor = $tutor;
		$this->member = $member;
	}
}
