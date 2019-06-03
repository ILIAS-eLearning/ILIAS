<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * Something that happened in the past, that is of importance to the business.
 */
interface DomainEvent {

	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return IdentifiesAggregate
	 */
	public function getAggregateId(): IdentifiesAggregate;
}