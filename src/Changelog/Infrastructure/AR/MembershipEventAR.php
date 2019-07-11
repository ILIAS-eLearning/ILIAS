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
	 * @var EventID
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      128
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
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 * @con_is_notnull  true
	 */
	protected $crs_obj_id;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      256
	 * @con_is_notnull  true
	 */
	protected $hist_crs_title;

	/**
	 * @return string
	 */
	public function getHistCrsTitle(): string {
		return $this->hist_crs_title;
	}

	/**
	 * @param string $hist_crs_title
	 */
	public function setHistCrsTitle(string $hist_crs_title) {
		$this->hist_crs_title = $hist_crs_title;
	}


	/**
	 * @return EventID
	 */
	public function getEventId(): EventID {
		return $this->event_id;
	}

	/**
	 * @param EventID $event_id
	 */
	public function setEventId(EventID $event_id) {
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
	 * @return int
	 */
	public function getCrsObjId(): int {
		return $this->crs_obj_id;
	}

	/**
	 * @param int $crs_obj_id
	 */
	public function setCrsObjId(int $crs_obj_id) {
		$this->crs_obj_id = $crs_obj_id;
	}

	/**
	 * @param $field_name
	 * @return string|null
	 */
	public function sleep($field_name) {
		switch ($field_name) {
			case 'event_id':
				return $this->event_id->getId();
			default:
				return null;
		}
	}

	/**
	 * @param $field_name
	 * @param $field_value
	 * @return EventID|null
	 */
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'event_id':
				return new EventID($field_value);
			default:
				return null;
		}
	}

}