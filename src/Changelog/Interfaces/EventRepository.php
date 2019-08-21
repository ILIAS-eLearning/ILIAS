<?php

namespace ILIAS\Changelog\Interfaces;

use ILIAS\Changelog\Query\EventDTO;
use ILIAS\Changelog\Query\Filter;
use ILIAS\Changelog\Query\Options;

/**
 * Interface Repository
 *
 * @package ILIAS\Changelog\Interfaces
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface EventRepository
{

    /**
     * @param Event $event
     *
     * @return void
     */
    public function storeEvent(Event $event);


    /**
     * @param Filter  $filter
     *
     * @param Options $options
     *
     * @return EventDTO[]
     */
    public function getEvents(Filter $filter, Options $options) : array;
}