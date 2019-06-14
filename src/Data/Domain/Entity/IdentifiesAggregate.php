<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain;

/**
 * An object that identifies an Aggregate. Typically a UUID, but any kind of id will do, as long as it is unique within the system.
 */
interface IdentifiesAggregate {

	/**
	 * Creates an identifier object from a string representation
	 *
	 * @param string $string
	 *
	 * @return IdentifiesAggregate
	 */
	public static function fromString(string $string): IdentifiesAggregate;


	/**
	 * Returns a string that can be parsed by fromString()
	 *
	 * @return string
	 */
	public function __toString(): string;


	/**
	 * Compares the object to another IdentifiesAggregate object. Returns true if both have the same type and value.
	 *
	 * @param IdentifiesAggregate $other
	 *
	 * @return boolean
	 */
	public function equals(IdentifiesAggregate $other): bool;
}
 