<?php

namespace ILIAS\Membership\Changelog\Interfaces;

use ILIAS\Membership\Changelog\Query\EventDTO;
use ILIAS\Membership\Changelog\Query\Filter;
use ILIAS\Membership\Changelog\Query\Options;
use ILIAS\Membership\Changelog\Query\Response;

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
     * @return Response
     */
    public function getEvents(Filter $filter, Options $options) : Response;
}