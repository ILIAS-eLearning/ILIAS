<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * An AggregateRoot, that can be reconstituted from an AggregateHistory.
 */
interface IsEventSourced {

	/**
	 * @param AggregateHistory $aggregate_history
	 *
	 * @return RecordsEvents
	 */
	public static function reconstituteFrom(AggregateHistory $aggregate_history): RecordsEvents;
}
 