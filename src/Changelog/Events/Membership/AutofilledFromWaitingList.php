<?php

namespace ILIAS\Changelog\Events\Membership;


use ILIAS\Changelog\Exception\CourseNotFoundException;
use ILIAS\Changelog\Exception\UserNotFoundException;
use ilObjCourse;
use ilObjUser;

/**
 * Class AutofilledFromWaitingList
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class AutofilledFromWaitingList extends MembershipEvent {

	const TYPE_ID = 9;

	/**
	 * @var int
	 */
	protected $crs_obj_id;
	/**
	 * @var int
	 */
	protected $added_user_id;
	/**
	 * @var int
	 */
	protected $acting_user_id;

	/**
	 * AutofilledFromWaitingList constructor.
	 * @param int $crs_obj_id
	 * @param int $added_user_id
	 * @param int $acting_user_id
	 * @throws CourseNotFoundException
	 * @throws UserNotFoundException
	 */
	public function __construct(int $crs_obj_id, int $added_user_id, int $acting_user_id) {
		if (!ilObjCourse::_exists($crs_obj_id)) {
			throw new CourseNotFoundException("couldn't find course with obj_id " . $crs_obj_id);
		}
		if (!ilObjUser::_exists($added_user_id)) {
			throw new UserNotFoundException("couldn't find user with id " . $added_user_id);
		}
		$this->crs_obj_id = $crs_obj_id;
		$this->added_user_id = $added_user_id;
		$this->acting_user_id = $acting_user_id;
	}

	/**
	 * @return int
	 */
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
	public function getAddedUserId(): int {
		return $this->added_user_id;
	}

	/**
	 * @return int
	 */
	public function getActingUserId(): int {
		return $this->acting_user_id;
	}


}