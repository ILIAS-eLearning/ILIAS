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
	 * @con_is_unique   true
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
	protected $type_id;

	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_is_notnull  true
	 * @con_length      8
	 */
	protected $actor_user_id;


	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   timestamp
	 * @con_is_notnull  true
	 */
	protected $timestamp;

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
	public function setTypeId(int $type_id) {
		$this->type_id = $type_id;
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
	public function setTimestamp(int $timestamp) {
		$this->timestamp = $timestamp;
	}

	/**
	 * @return int
	 */
	public function getActorUserId(): int {
		return $this->actor_user_id;
	}

	/**
	 * @param int $actor_user_id
	 */
	public function setActorUserId(int $actor_user_id) {
		$this->actor_user_id = $actor_user_id;
	}


	/**
	 * @param $field_name
	 * @return string|null
	 */
	public function sleep($field_name) {
		switch ($field_name) {
			case 'event_id':
				return $this->event_id->getId();
			case 'timestamp':
				return date('Y-m-d H:i:s', $this->timestamp);
			default:
				return null;
		}
	}

	/**
	 * @param $field_name
	 * @param $field_value
	 * @return EventID|null
	 * @throws \Exception
	 */
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'event_id':
				return new EventID($field_value);
			case 'timestamp':
				return strtotime($field_value);
			default:
				return null;
		}
	}


}