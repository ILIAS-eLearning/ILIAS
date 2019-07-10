<?php

namespace ILIAS\Changelog\Infrastructure\AR;


use ActiveRecord;

/**
 * Class MembershipRequested
 * @package ILIAS\Changelog\Membership\AR
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipEventAR extends ActiveRecord {

	const TABLE_NAME = 'changelog_membership';

	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}

	/**
	 * @var int
	 *
	 * @con_is_primary  true
	 * @con_sequence    true
	 * @con_is_unique   true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      8
	 */
	protected $id;

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
	protected $member_user_id;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      128
	 * @con_is_notnull  true
	 */
	protected $member_login;

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
	public function setEventId(int $event_id) {
		$this->event_id = $event_id;
	}

	/**
	 * @return int
	 */
	public function getMemberUserId(): int {
		return $this->member_user_id;
	}

	/**
	 * @param int $member_user_id
	 */
	public function setMemberUserId(int $member_user_id) {
		$this->member_user_id = $member_user_id;
	}

	/**
	 * @return string
	 */
	public function getMemberLogin(): string {
		return $this->member_login;
	}

	/**
	 * @param string $member_login
	 */
	public function setMemberLogin(string $member_login) {
		$this->member_login = $member_login;
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
	public function setObjId(int $obj_id) {
		$this->obj_id = $obj_id;
	}
}