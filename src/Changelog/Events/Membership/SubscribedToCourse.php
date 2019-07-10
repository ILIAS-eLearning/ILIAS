<?php

namespace ILIAS\Changelog\Events\Membership;


/**
 * Class SubscribedToCourse
 * @package ILIAS\Changelog\Membership\Events
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
	 */
	public function __construct(int $crs_obj_id, int $subscribing_user_id) {
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