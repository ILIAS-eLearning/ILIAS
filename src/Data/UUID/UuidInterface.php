<?php

namespace ILIAS\Data\UUID;

/**
 * Class UuidInterface
 * @package ILIAS\Data\UUID
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface UuidInterface {

	/**
	 * Compares this UUID to the specified UUID.
	 *
	 * The first of two UUIDs is greater than the second if the most
	 * significant field in which the UUIDs differ is greater for the first
	 * UUID.
	 *
	 * * Q. What's the value of being able to sort UUIDs?
	 * * A. Use them as keys in a B-Tree or similar mapping.
	 *
	 * @param UuidInterface $other UUID to which this UUID is compared
	 * @return int -1, 0 or 1 as this UUID is less than, equal to, or greater than `$uuid`
	 */
	public function compareTo(UuidInterface $other): int;

	/**
	 * Compares this object to the specified object.
	 *
	 * The result is true if and only if the argument is not null, is a UUID
	 * object, has the same variant, and contains the same value, bit for bit,
	 * as this UUID.
	 *
	 * @param UuidInterface $other
	 * @return bool True if `$other` is equal to this UUID
	 */
	public function equals(UuidInterface $other): bool;

	/**
	 * Converts this UUID into a string representation.
	 *
	 * @return string
	 */
	public function toString(): string;

}