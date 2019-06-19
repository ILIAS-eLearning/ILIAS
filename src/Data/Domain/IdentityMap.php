<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Entity\TracksChanges;
use ILIAS\Data\Domain\Exception\DomainExceptionMultipleInstancesOfAggregateDetected;

/**
 * Holds unique Aggregate instances in memory, mapped by id. Useful to make sure an Aggregate is not loaded twice.
 */
interface IdentityMap {

	/**
	 * @param TracksChanges $aggregate
	 *
	 * @return void
	 * @throws DomainExceptionMultipleInstancesOfAggregateDetected
	 */
	public function attach(TracksChanges $aggregate): void;


	/**
	 * @param AggregateId $aggregateId
	 *
	 * @return TracksChanges | null
	 */
	public function find(AggregateId $aggregateId) /*:TracksChanges|nul*/ ;
}