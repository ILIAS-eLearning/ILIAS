<?php

namespace ILIAS\Changelog;

use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventRepository;
use ILIAS\Changelog\Query\QueryService;

/**
 * Class ChangelogService
 *
 * @package ILIAS\Changelog
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogService
{

    /**
     * @var EventRepository[]
     */
    protected $repositories;


    /**
     * ChangelogService constructor.
     *
     * @param EventRepository $event_repository
     */
    public function __construct(EventRepository $event_repository)
    {
        $this->registerRepository($event_repository);
    }


    /**
     * Use to add additional Repositories to store the event. Default is the ilDBEventRepository.
     *
     * @param EventRepository $event_repository
     */
    public function registerRepository(EventRepository $event_repository)
    {
        $this->repositories[] = $event_repository;
    }


    /**
     * @param Event $event
     */
    public function logEvent(Event $event)
    {
        foreach ($this->repositories as $repository) {
            $repository->storeEvent($event);
        }
    }


    /**
     * @return QueryService
     */
    public function query() : QueryService
    {
        return new QueryService();
    }
}