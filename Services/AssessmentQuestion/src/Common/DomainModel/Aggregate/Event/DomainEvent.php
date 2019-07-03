<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace  ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event;

use AggregateRevision;
use \ilDateTime;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateId;

/**
 * Something that happened in the past, that is of importance to the business.
 */
interface DomainEvent {

	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return AggregateId
	 */
	public function getAggregateId(): AggregateId;

	/**
	 * @return string
	 */
	public function getEventName(): string;


	/**
	 * @return ilDateTime
	 */
	public function getOccurredOn() : ilDateTime;


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int;


	/**
	 * @return string
	 */
	public function getEventBody(): string;
}