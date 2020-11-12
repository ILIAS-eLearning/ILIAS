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
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->jobs;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return iterator_count($this);
    }

    /**
     * @inheritdoc
     */
    public function add(ilCronJobEntity $job) : void
    {
        $this->jobs->append($job);
    }

    /**
     * @inheritdoc
     */
    public function filter(callable $callable) : ilCronJobCollection
    {
        return new static(array_filter(iterator_to_array($this), $callable));
    }

    /**
     * @inheritdoc
     */
    public function slice(int $offset, ?int $length = null) : ilCronJobCollection
    {
        return new static(array_slice(iterator_to_array($this), $offset, $length, true));
    }

    /**
     * @inheritdoc
     */
    public function toArray() : array
    {
        return iterator_to_array($this);
    }
}
