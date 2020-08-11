<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronJobEntities
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilCronJobEntities implements ilCronJobCollection
{
    /** @var ArrayIterator */
    private $jobs;

    /**
     * ilCronJobs constructor.
     * @param array $jobs
     */
    public function __construct(array $jobs = [])
    {
        $this->jobs = new ArrayIterator($jobs);
    }

    /**
     * @ineritdoc
     */
    public function getIterator()
    {
        return $this->jobs;
    }

    /**
     * @ineritdoc
     */
    public function count()
    {
        return iterator_count($this);
    }

    /**
     * @ineritdoc
     */
    public function add(ilCronJobEntity $job) : void
    {
        $this->jobs->append($job);
    }

    /**
     * @ineritdoc
     */
    public function filter(callable $callable) : ilCronJobCollection
    {
        return new static(array_filter(iterator_to_array($this), $callable));
    }

    /**
     * @ineritdoc
     */
    public function slice(int $offset, ?int $length = null) : ilCronJobCollection
    {
        return new static(array_slice(iterator_to_array($this), $offset, $length, true));
    }

    /**
     * @ineritdoc
     */
    public function toArray() : array
    {
        return iterator_to_array($this);
    }
}
