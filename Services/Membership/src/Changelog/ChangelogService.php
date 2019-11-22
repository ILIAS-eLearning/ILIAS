<?php

namespace ILIAS\Membership\Changelog;

use ILIAS\Membership\Changelog\Interfaces\Event;
use ILIAS\Membership\Changelog\Interfaces\EventRepository;
use ILIAS\Membership\Changelog\Query\EventDTO;
use ILIAS\Membership\Changelog\Query\Filter;
use ILIAS\Membership\Changelog\Query\Options;
use ILIAS\Membership\Changelog\Query\QueryFactory;

/**
 * Class ChangelogService
 *
 * @package ILIAS\Membership\Changelog
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
     * ChangelogService constructor.
     *
     * @param EventRepository $event_repository
     */
    public function __construct(EventRepository $event_repository)
    {
        $this->repository = $event_repository;
    }


    /**
     * @param Event $event
     */
    public function log(Event $event) : void
    {
        $this->repository->storeEvent($event);
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
     * @return QueryFactory
     */
    public function queryFactory() : QueryFactory
    {
        return QueryFactory::getInstance();
    }
}