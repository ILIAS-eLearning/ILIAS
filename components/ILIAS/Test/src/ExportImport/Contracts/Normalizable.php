<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Test\ExportImport\Contracts;

/**
 * Provides a contract for objects capable of self-contained normalization and denormalization.
 *
 * This interface allows an object to define its own transformation into a language-neutral,
 * intermediate array structure. This array serves as a stable representation for further
 * processing, such as serialization into transport formats like XML or JSON during
 * export/import operations.
 * 
 * A normalized array must only contain scalar values (string, int, float, bool) and nested 
 * normalized arrays. 
 * 
 * @phpstan-type NormalizedArray array<array-key, scalar|NormalizedArray>
 *
 * ---
 * IMPORTANT: Implementations of this interface MUST be self-contained.
 * The methods should operate solely on the object's internal state and the provided
 * data array, without relying on external services, database connections, or any other context.
 * For context-dependent normalization, see `ContextualNormalizable`-Contract.
 * ---
 */
interface Normalizable
{
    /**
     * Normalizes the object into a simple, associative array.
     *
     * The resulting array represents the object's state and should contain only
     * scalar values, arrays, or other Normalizable objects.
     *
     * @return NormalizedArray The normalized representation of the object
     */
    public function normalize(): array;

    /**
     * Denormalizes an array back into an instance of the object.
     *
     * This static factory method is responsible for constructing a new object instance
     * from the provided array data. It should validate the input and may throw an
     * exception if the data is incomplete or malformed.
     *
     * @param NormalizedArray $data The data to restore the object from
     * @return static A new instance of the class
     */
    public static function denormalize(array $data): static;
}
