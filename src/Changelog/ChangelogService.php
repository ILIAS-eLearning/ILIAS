<?php

namespace ILIAS\Changelog;

use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventRepository;
use ILIAS\Changelog\Query\EventDTO;
use ILIAS\Changelog\Query\Filter;
use ILIAS\Changelog\Query\Options;
use ILIAS\Changelog\Query\QueryBuilder;

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
     * @var EventRepository
     */
    protected $repository;
    /**
     * @var EventRepository[]
     */
    protected $additional_write_repositories;


    /**
     * ChangelogService constructor.
     *
     * @param EventRepository $event_repository
     */
    public function __construct(EventRepository $event_repository)
    {
        $this->repository = $event_repository;
    }


    /**
     * Use to add additional Repositories to store the event. Default is the ilDBEventRepository.
     *
     * @param EventRepository $event_repository
     */
    public function registerAdditionalWriteRepository(EventRepository $event_repository)
    {
        $this->additional_write_repositories[] = $event_repository;
    }


    /**
     * @param Event $event
     */
    public function log(Event $event)
    {
        $this->repository->storeEvent($event);
        foreach ($this->additional_write_repositories as $repository) {
            $repository->storeEvent($event);
        }
    }


    /**
     * @param Filter  $filter
     *
     * @param Options $options
     *
     * @return EventDTO[]
     */
    public function query(Filter $filter, Options $options) : array
    {
        return $this->repository->getEvents($filter, $options);
    }


    /**
     * @return QueryBuilder
     */
    public function queryBuilder() : QueryBuilder
    {
        return QueryBuilder::getInstance();
    }
}