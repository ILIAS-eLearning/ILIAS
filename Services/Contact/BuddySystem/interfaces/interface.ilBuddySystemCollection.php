<?php declare(strict_types=1);

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
 */
interface ilBuddySystemCollection extends Countable, IteratorAggregate, ArrayAccess
{
    /**
     * Adds an element at the end of the collection.
     * @param mixed $element
     */
    public function add($element) : void;

    /**
     * @param string|int $key The index of the element to remove.
     * @throws InvalidArgumentException
     */
    public function remove($key) : void;

    /**
     * @param mixed $element The element to remove.
     * @throws InvalidArgumentException
     */
    public function removeElement($element) : void;

    /**
     * @param string|int $key The index to check for.
     * @return bool true if the collection contains the element, false otherwise.
     */
    public function containsKey($key) : bool;

    /**
     * @param mixed $element The element
     * @return string|int The index of the element.
     */
    public function getKey($element);

    /**
     * Clears the list
     */
    public function clear() : void;

    /**
     * @param mixed $element
     * @return bool true if the collection contains the element, false otherwise.
     */
    public function contains($element) : bool;

    /**
     * @param string|int $key The index of the element to get.
     * @return mixed
     */
    public function get($key);

    /**
     * @param string|int $key The index of the element to set.
     * @param mixed       $value
     */
    public function set($key, $value) : void;

    /**
     * @return bool true if the collection is empty, false otherwise.
     */
    public function isEmpty() : bool;

    /**
     * Gets all indices of the collection.
     * @return array The indices of the collection, in the order of the corresponding elements in the collection.
     */
    public function getKeys() : array;

    /**
     * Gets all values of the collection.
     * @return array The values of all elements in the collection, in the order they appear in the collection.
     */
    public function getValues() : array;

    /**
     * Returns all the elements of this collection that satisfy the predicate $callable.
     * @param callable $callable
     * @return self
     */
    public function filter(callable $callable) : self;

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Calling this method will only return the selected slice and NOT change the elements contained in the collection slice is called on.
     * @param int $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     * @return self
     */
    public function slice(int $offset, int $length = null) : self;

    /**
     * @return array
     */
    public function toArray() : array;

    /**
     * @param mixed $other
     * @return bool
     */
    public function equals($other) : bool;
}
