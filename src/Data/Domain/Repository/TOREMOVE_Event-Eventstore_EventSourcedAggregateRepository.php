<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * A collection-oriented Repository for eventsourced Aggregates.
 */
interface EventSourcedAggregateRepository {

	/**
	 * @param IdentifiesAggregate $aggregate_Id
	 *
	 * @return RecordsEvents
	 */
	public function get(IdentifiesAggregate $aggregate_Id): RecordsEvents;


	/**
	 * @param RecordsEvents $aggregate
	 *
	 * @return void
	 */
	public function add(RecordsEvents $aggregate): void;
}