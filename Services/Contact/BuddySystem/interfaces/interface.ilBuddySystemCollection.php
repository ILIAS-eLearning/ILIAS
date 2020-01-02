<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    public function add($element);

    /**
     * @param string|integer $key The index of the element to remove.
     * @throws InvalidArgumentException
     */
    public function remove($key);

    /**
     * @param mixed $element The element to remove.
     * @throws InvalidArgumentException
     */
    public function removeElement($element);

    /**
     * @param string|integer $key The index to check for.
     * @return boolean true if the collection contains the element, false otherwise.
     */
    public function containsKey($key);

    /**
     * @param mixed $element The element
     * @return string|integer The index of the element.
     */
    public function getKey($element);

    /**
     * Clears the list
     */
    public function clear();

    /**
     * @param mixed $element
     * @return boolean true if the collection contains the element, false otherwise.
     */
    public function contains($element);

    /**
     * @param string|integer $key The index of the element to get.
     * @return mixed
     */
    public function get($key);

    /**
     * @param string |integer $key The index of the element to set.
     * @param mixed           $value
     */
    public function set($key, $value);

    /**
     * @return boolean true if the collection is empty, false otherwise.
     */
    public function isEmpty();

    /**
     * Gets all indices of the collection.
     * @return array The indices of the collection, in the order of the corresponding elements in the collection.
     */
    public function getKeys();

    /**
     * Gets all values of the collection.
     * @return array The values of all elements in the collection, in the order they appear in the collection.
     */
    public function getValues();

    /**
     * Returns all the elements of this collection that satisfy the predicate $callable.
     * @param Closure $closure
     * @return ilBuddySystemCollection
     */
    public function filter(Closure $closure);

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Calling this method will only return the selected slice and NOT change the elements contained in the collection slice is called on.
     * @param int      $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     * @return ilBuddySystemCollection
     */
    public function slice($offset, $length = null);

    /**
     * @return array
     */
    public function toArray();
}
