<?php

namespace ILIAS\Changelog\Infrastructure\AR;


use ActiveRecord;

/**
 * Class EventAR
 * @package ILIAS\Changelog\Infrastructure\AR
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class EventAR extends ActiveRecord {

	const TABLE_NAME = 'changelog_events';

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
	 * @con_length      4
	 * @con_is_notnull  true
	 */
	protected $type_id;

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
	 * @con_fieldtype   timestamp
	 * @con_is_notnull  true
	 */
	protected $timestamp;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getTypeId(): int {
		return $this->type_id;
	}

	/**
	 * @param int $type_id
	 */
	public function setTypeId(int $type_id): void {
		$this->type_id = $type_id;
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
	public function getTimestamp(): int {
		return $this->timestamp;
	}

	/**
	 * @param int $timestamp
	 */
	public function setTimestamp(int $timestamp): void {
		$this->timestamp = $timestamp;
	}


}