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

interface ilCronJobCollection extends Countable, IteratorAggregate
{
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
