<?php

namespace ILIAS\Changelog\Membership\Events;


use ILIAS\Changelog\Membership\MembershipEvent;

/**
 * Class MembershipRequested
 * @package ILIAS\Changelog\Membership\Events
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequested extends MembershipEvent {

	const TYPE_ID = 1;
	/**
	 * @var int
	 */
	protected $crs_obj_id;
	/**
	 * @var int
	 */
	protected $requesting_user_id;

	/**
	 * MembershipRequested constructor.
	 * @param int $crs_obj_id
	 * @param int $requesting_user_id
	 */
	public function __construct(int $crs_obj_id, int $requesting_user_id) {
		$this->crs_obj_id = $crs_obj_id;
		$this->requesting_user_id = $requesting_user_id;
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
	public function getRequestingUserId(): int {
		return $this->requesting_user_id;
	}




}