<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * An object that records the events that happened to it since the last time it was cleared, or since it was
 * restored from persistence.
 */
interface RecordsEvents {

	/**
	 * Get all the Domain Events that were recorded since the last time it was cleared, or since it was
	 * restored from persistence. This does not include events that were recorded prior.
	 *
	 * @return DomainEvents
	 */
	public function getRecordedEvents(): DomainEvents;


	/**
	 * Clears the record of new Domain Events. This doesn't clear the history of the object.
	 *
	 * @return void
	 */
	public function clearRecordedEvents(): void;
}