<?php

namespace ILIAS\Changelog\Events\Membership;


use ILIAS\Changelog\Exception\CourseNotFoundException;
use ILIAS\Changelog\Exception\UserNotFoundException;
use ilObjCourse;
use ilObjUser;

/**
 * Class SubscribedToCourse
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class SubscribedToCourse extends MembershipEvent {

	const TYPE_ID = 5;


	/**
	 * @var int
	 */
	protected $crs_obj_id;
	/**
	 * @var int
	 */
	protected $subscribing_user_id;

	/**
	 * MembershipRequested constructor.
	 * @param int $crs_obj_id
	 * @param int $subscribing_user_id
	 * @throws CourseNotFoundException
	 * @throws UserNotFoundException
	 */
	public function __construct(int $crs_obj_id, int $subscribing_user_id) {
		if (!ilObjCourse::_exists($crs_obj_id)) {
			throw new CourseNotFoundException("couldn't find course with obj_id " . $crs_obj_id);
		}
		if (!ilObjUser::_exists($subscribing_user_id)) {
			throw new UserNotFoundException("couldn't find user with id " . $subscribing_user_id);
		}
		$this->crs_obj_id = $crs_obj_id;
		$this->subscribing_user_id = $subscribing_user_id;
	}


	public function getTypeId(): int {
		return self::TYPE_ID;
	}

	/**
	 * @return int
	 */
	public function getCrsObjId(): int {
		return $this->crs_obj_id;
	}

	/**
	 * @return int
	 */
	public function getSubscribingUserId(): int {
		return $this->subscribing_user_id;
	}

}