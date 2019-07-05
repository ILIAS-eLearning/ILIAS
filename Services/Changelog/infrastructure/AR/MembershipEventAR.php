<?php

namespace ILIAS\Changelog\Membership\AR;


use ActiveRecord;

/**
 * Class MembershipRequested
 * @package ILIAS\Changelog\Membership\AR
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipEventAR extends ActiveRecord {

	const TABLE_NAME = 'changelog_membership';

	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      8
	 */
	protected $event_id;

	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 * @con_is_notnull  true
	 */
	protected $user_id;

	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 * @con_is_notnull  true
	 */
	protected $obj_id;

	/**
	 * @return int
	 */
	public function getEventId(): int {
		return $this->event_id;
	}

	/**
	 * @param int $event_id
	 */
	public function setEventId(int $event_id): void {
		$this->event_id = $event_id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId(int $user_id): void {
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->obj_id;
	}

	/**
	 * @param int $obj_id
	 */
	public function setObjId(int $obj_id): void {
		$this->obj_id = $obj_id;
	}
}