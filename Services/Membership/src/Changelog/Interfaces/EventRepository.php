<?php

namespace ILIAS\Membership\Changelog\Interfaces;

use ILIAS\Membership\Changelog\Query\EventDTO;
use ILIAS\Membership\Changelog\Query\Filter;
use ILIAS\Membership\Changelog\Query\Options;

/**
 * Interface Repository
 *
 * @package ILIAS\Membership\Changelog\Interfaces
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
    public function storeEvent(Event $event) : void;


    /**
     * @param Filter  $filter
     *
     * @param Options $options
     *
     * @return EventDTO[]
     */
    public function getEvents(Filter $filter, Options $options) : array;
}