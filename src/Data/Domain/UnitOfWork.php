<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * A unit of work that acts both as an identity map and a change tracker.  It does not commit/save/persist. Its role is
 * reduced to tracking multiple aggregates, and to hand you back those that have changed. Persisting the ones that have
 * changed, happens on the outside.
 */
interface UnitOfWork extends IdentityMap {

	/**
	 * Returns AggregateRoots that have changed.
	 *
	 * @return TracksChanges[]
	 */
	public function getChanges(): array;
}
 