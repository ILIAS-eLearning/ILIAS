<?php

namespace ILIAS\Changelog\Events\Membership;


use ILIAS\Changelog\Exception\CourseNotFoundException;
use ILIAS\Changelog\Exception\UserNotFoundException;
use ilObjCourse;
use ilObjUser;

/**
 * Class RemovedFromCourse
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class RemovedFromCourse extends MembershipEvent {

	const TYPE_ID = 6;


	/**
	 * @var int
	 */
	protected $crs_obj_id;
	/**
	 * @var int
	 */
	protected $member_user_id;
	/**
	 * @var int
	 */
	protected $removing_user_id;

	/**
	 * MembershipRequested constructor.
	 * @param int $crs_obj_id
	 * @param int $member_user_id
	 * @param int $removing_user_id
	 * @throws CourseNotFoundException
	 * @throws UserNotFoundException
	 */
	public function __construct(int $crs_obj_id, int $member_user_id, int $removing_user_id) {
		if (!ilObjCourse::_exists($crs_obj_id)) {
			throw new CourseNotFoundException("couldn't find course with obj_id " . $crs_obj_id);
		}
		if (!ilObjUser::_exists($member_user_id)) {
			throw new UserNotFoundException("couldn't find user with id " . $member_user_id);
		}
		if (!ilObjUser::_exists($removing_user_id)) {
			throw new UserNotFoundException("couldn't find user with id " . $removing_user_id);
		}
		$this->crs_obj_id = $crs_obj_id;
		$this->member_user_id = $member_user_id;
		$this->removing_user_id = $removing_user_id;
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
	public function getMemberUserId(): int {
		return $this->member_user_id;
	}

	/**
	 * @return int
	 */
	public function getRemovingUserId(): int {
		return $this->removing_user_id;
	}
}