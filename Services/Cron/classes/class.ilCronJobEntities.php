<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronJobEntities
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilCronJobEntities implements ilCronJobCollection
{
    private ArrayIterator $jobs;

    /**
     * ilCronJobs constructor.
     * @param ilCronJobEntity[] $jobs
     */
    public function __construct(array $jobs = [])
    {
        $this->jobs = new ArrayIterator($jobs);
    }

    /**
     * @return ArrayIterator|ilCronJobEntity[]
     */
    public function getIterator() : ArrayIterator
    {
        return $this->jobs;
    }

    public function count() : int
    {
        return iterator_count($this);
    }

    public function add(ilCronJobEntity $job) : void
    {
        $this->jobs->append($job);
    }

    public function filter(callable $callable) : ilCronJobCollection
    {
        return new static(array_filter(iterator_to_array($this), $callable));
    }

    public function slice(int $offset, ?int $length = null) : ilCronJobCollection
    {
        return new static(array_slice(iterator_to_array($this), $offset, $length, true));
    }

    /**
     * @return ilCronJobEntity[]
     */
    public function toArray() : array
    {
        return iterator_to_array($this);
    }
}
