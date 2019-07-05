<?php

namespace ILIAS\Changelog\Membership\Events;


use ILIAS\Changelog\Membership\MembershipEvent;

/**
 * Class UnsubscribedFromCourse
 * @package ILIAS\Changelog\Membership\Events
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class UnsubscribedFromCourse extends MembershipEvent {

	const TYPE_ID = 4;

	/**
	 * @var int
	 */
	protected $crs_obj_id;
	/**
	 * @var int
	 */
	protected $unsubscribing_user_id;

	/**
	 * MembershipRequested constructor.
	 * @param int $crs_obj_id
	 * @param int $unsubscribing_user_id
	 */
	public function __construct(int $crs_obj_id, int $unsubscribing_user_id) {
		$this->crs_obj_id = $crs_obj_id;
		$this->unsubscribing_user_id = $unsubscribing_user_id;
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
	public function getUnsubscribingUserId(): int {
		return $this->unsubscribing_user_id;
	}
}