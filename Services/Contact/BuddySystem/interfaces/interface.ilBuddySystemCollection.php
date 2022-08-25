<?php

declare(strict_types=1);

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

/**
 * Interface ilBuddySystemCollection
 * @author Michael Jansen <mjansen@databay.de>
 * @template T
 * @template TKey
 * @template-extends IteratorAggregate<TKey, T>
 * @template-extends ArrayAccess<TKey, T>
 */
interface ilBuddySystemCollection extends Countable, IteratorAggregate, ArrayAccess
{
    /**
     * Adds an element at the end of the collection.
     * @phpstan-param T $element
     */
    public function add(mixed $element): void;

    /**
     * @param string|int $key The index of the element to remove.
     * @phpstan-param TKey $key
     * @throws InvalidArgumentException
     */
    public function remove(string|int $key): void;

    /**
     * @param mixed $element The element to remove.
     * @phpstan-param T $element
     * @throws InvalidArgumentException
     */
    public function removeElement(mixed $element): void;

    /**
     * @param string|int $key The index to check for.
     * @phpstan-param TKey $key
     * @return bool true if the collection contains the element, false otherwise.
     */
    public function containsKey(string|int $key): bool;

    /**
     * @param mixed $element The element
     * @phpstan-param T $element
     * @return string|int The index of the element.
     * @phpstan-return TKey
     */
    public function getKey(mixed $element): string|int;

    /**
     * Clears the list
     */
    public function clear(): void;

    /**
     * @phpstan-param T $element
     * @return bool true if the collection contains the element, false otherwise.
     */
    public function contains(mixed $element): bool;

    /**
     * @param string|int $key The index of the element to get.
     * @phpstan-param TKey $key
     * @phpstan-return T|null
     */
    public function get(string|int $key): mixed;

    /**
     * @param string|int $key The index of the element to set.
     * @phpstan-param TKey $key
     * @phpstan-param T $value
     */
    public function set(string|int $key, mixed $value): void;

    /**
     * @return bool true if the collection is empty, false otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Gets all indices of the collection.
     * @return string[]|int[] The indices of the collection, in the order of the corresponding elements in the collection.
     * @phpstan-return list<TKey>
     */
    public function getKeys(): array;

    /**
     * Gets all values of the collection.
     * @return list<mixed> The values of all elements in the collection, in the order they appear in the collection.
     * @phpstan-return list<T>
     */
    public function getValues(): array;

    /**
     * Returns all the elements of this collection that satisfy the predicate $callable.
     */
    public function filter(callable $callable): self;

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Calling this method will only return the selected slice and NOT change the elements contained in the collection slice is called on.
     * @param int $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     */
    public function slice(int $offset, int $length = null): self;

    /**
     * @return array<int|string, mixed>
     * @psalm-return array<TKey, T>
     */
    public function toArray(): array;

    public function equals(mixed $other): bool;
}
