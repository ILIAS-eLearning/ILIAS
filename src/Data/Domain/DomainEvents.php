<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

class DomainEvents extends ImmutableArray {

	/**
	 * Throw when the type of item is not accepted.
	 *
	 * @param $item
	 *
	 * @return void
	 * @throws DomainExceptionArrayIsImmutable
	 */
	protected function guardType($item): void {
		if (!($item instanceof DomainEvent)) {
			throw new DomainExceptionArrayIsImmutable;
		}
	}
}