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

namespace ILIAS\KeyValueStorage;

use ILIAS\Refinery\Transformation;

/**
 * Key-value store scoped to one namespace.
 *
 * This is application state, not a cache: values are kept until they are
 * changed or cleared. Use ILIAS\Cache for data that may be dropped at any time.
 */
interface Store
{
    /**
     * Reads the value along, so that a following get() does not read again.
     *
     * @throws \InvalidArgumentException if the key is invalid
     * @throws Exception\InvalidStoredValueException if the stored value cannot be read back
     */
    public function has(string $key): bool;

    /**
     * Reads a value and applies a Refinery transformation, like HTTP request wrappers.
     *
     * Absent keys are passed to the transformation as {@code null}.
     *
     * @throws \InvalidArgumentException if the key is invalid
     * @throws Exception\InvalidStoredValueException if the stored value cannot be read back
     */
    public function get(string $key, Transformation $transformation): mixed;

    /**
     * Reads several known keys, each with its own Refinery transformation.
     *
     * The result has the same keys as the given map, in the same order. Absent
     * keys are passed to their transformation as {@code null}, like
     * {@see self::get()}. Keys that the namespace holds besides those named
     * here are left out.
     *
     * @param array<string, Transformation> $transformations
     * @return array<string, mixed>
     * @throws \InvalidArgumentException if a key is invalid or an entry is not a transformation
     * @throws Exception\InvalidStoredValueException if a stored value cannot be read back
     */
    public function getMany(array $transformations): array;

    /**
     * The keys this namespace currently holds, sorted as strings.
     *
     * @return list<string>
     */
    public function keys(): array;

    /**
     * @throws \InvalidArgumentException if the key is invalid or the value cannot be stored
     */
    public function set(string $key, mixed $value): void;

    /**
     * @throws \InvalidArgumentException if the key is invalid
     */
    public function delete(string $key): void;

    /**
     * Removes every entry of this namespace, and only of this namespace.
     */
    public function clear(): void;
}
