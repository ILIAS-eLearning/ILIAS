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

class ilCronJobEntities implements ilCronJobCollection
{
    private ArrayIterator $jobs;

    public function __construct(ilCronJobEntity ...$jobs)
    {
        $this->jobs = new ArrayIterator($jobs);
    }

    /**
     * @return ArrayIterator|ilCronJobEntity[]
     */
    public function getIterator(): ArrayIterator
    {
        return $this->jobs;
    }

    public function count(): int
    {
        return iterator_count($this);
    }

    public function add(ilCronJobEntity $job): void
    {
        $this->jobs->append($job);
    }

    public function filter(callable $callable): ilCronJobCollection
    {
        return new static(...array_filter(iterator_to_array($this), $callable));
    }

    public function slice(int $offset, ?int $length = null): ilCronJobCollection
    {
        return new static(...array_slice(iterator_to_array($this), $offset, $length, true));
    }

    /**
     * @return ilCronJobEntity[]
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }
}
