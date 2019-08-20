<?php

namespace ILIAS\Changelog\Interfaces;

/**
 * Interface Repository
 *
 * @package ILIAS\Changelog\Interfaces
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface EventRepository
{

    // WRITING

    /**
     * @param Event $event
     *
     * @return void
     */
    public function storeEvent(Event $event);


    // READING



}