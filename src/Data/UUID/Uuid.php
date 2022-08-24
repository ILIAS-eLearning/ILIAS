<?php

declare(strict_types=1);

namespace ILIAS\Data\UUID;

/**
 * Class UuidInterface
 * @package ILIAS\Data\UUID
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface Uuid
{
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
     * @param Uuid $other UUID to which this UUID is compared
     * @return int -1, 0 or 1 as this UUID is less than, equal to, or greater than `$uuid`
     */
    public function compareTo(Uuid $other): int;

    /**
     * Compares this object to the specified object.
     *
     * The result is true if and only if the argument is not null, is a UUID
     * object, has the same variant, and contains the same value, bit for bit,
     * as this UUID.
     *
     * @param Uuid $other
     * @return bool True if `$other` is equal to this UUID
     */
    public function equals(Uuid $other): bool;

    /**
     * Converts this UUID into a string representation.
     */
    public function toString(): string;

    /**
     * Enforce that UUID implementation implement the __toString() magic method
     */
    public function __toString(): string;
}
