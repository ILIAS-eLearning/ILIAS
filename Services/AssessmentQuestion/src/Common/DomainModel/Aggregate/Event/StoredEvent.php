<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateId;

/**
 * Interface StoredEvent
 *
 * @package ILIAS\AssessmentQuestion\Common\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface StoredEvent {

	/**
	 * @return string
	 */
	static function returnDbTableName();


	/**
	 * @return int
	 */
	public function getEventId(): int;


	public function getAggregateId(): AggregateId;


	/**
	 * @return string
	 */
	public function getEventName(): string;


	/**
	 * @return int
	 */
	public function getOccuredOn(): int;


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int;


	/**
	 * @return string
	 */
	public function getEventBody(): string;


	/**
	 * @return void
	 */
	public function create(): void;
}
