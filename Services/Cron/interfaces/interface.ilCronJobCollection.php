<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronJobCollection
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilCronJobCollection extends Countable, IteratorAggregate
{
    /**
     * @param ilCronJobEntity $job
     */
    public function add(ilCronJobEntity $job) : void;

    /**
     * Returns all the elements of this collection that satisfy the predicate $callable.
     * @param callable $callable
     * @return self
     */
    public function filter(callable $callable) : ilCronJobCollection;

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Calling this method will only return the selected slice and NOT change the elements contained in the collection slice is called on.
     * @param int $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     * @return self
     */
    public function slice(int $offset, ?int $length = null) : ilCronJobCollection;

    /**
     * @return ilCronJobEntity[]
     */
    public function toArray() : array;
}
