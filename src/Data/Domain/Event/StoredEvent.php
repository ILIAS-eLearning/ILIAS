<?php

namespace ILIAS\Data\Domain;

/**
 * Class StoredEvent
 *
 * @author Martin Studer <ms@studer-raimann.ch>
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


	public function getAggregateId(): IdentifiesAggregate;


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
