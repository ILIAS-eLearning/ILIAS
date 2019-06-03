<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * An AggregateRoot that exposes whether it was changed.
 */
interface TracksChanges {

	/**
	 * @return IdentifiesAggregate
	 */
	public function getAggregateId(): IdentifiesAggregate;


	/**
	 * @return bool
	 */
	public function hasChanges(): bool;
}
 