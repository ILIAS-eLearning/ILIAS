<?php

namespace ILIAS\Membership\Changelog\Query;

/**
 * Class Response
 *
 * @package ILIAS\Membership\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Response
{

    /**
     * @var EventDTO[]
     */
    protected $events;
    /**
     * @var int
     */
    protected $max_count;


    /**
     * Response constructor.
     *
     * @param EventDTO[] $events
     * @param int        $max_count
     */
    public function __construct(array $events, int $max_count)
    {
        $this->events = $events;
        $this->max_count = $max_count;
    }


    /**
     * @return EventDTO[]
     */
    public function getEvents() : array
    {
        return $this->events;
    }


    /**
     * @return int
     */
    public function getMaxCount() : int
    {
        return $this->max_count;
    }


}