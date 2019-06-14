<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

abstract class AbstractDomainEvent implements DomainEvent {


	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return IdentifiesAggregate
	 */
	abstract public function getAggregateId(): IdentifiesAggregate;


	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
	 * e.g. 'question.created'
	 */
	abstract public function getEventName():string {
		return self::EVENT_NAME;
	}
}