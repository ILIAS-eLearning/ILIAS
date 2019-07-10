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
	 * @var int
	 *
	 * @con_is_unique   true
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
	protected $type_id;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      128
	 * @con_is_notnull  true
	 */
	protected $actor_login;

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
	 * @return string
	 */
	public function getActorLogin(): string {
		return $this->actor_login;
	}

	/**
	 * @param string $actor_login
	 */
	public function setActorLogin(string $actor_login) {
		$this->actor_login = $actor_login;
	}

}