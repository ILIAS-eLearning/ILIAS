<?php

namespace ILIAS\Changelog\Events\GlobalEvents;

use ILIAS\Changelog\Interfaces\Event;

/**
 * Class GlobalEvent
 *
 * @package ILIAS\Changelog\Events\GlobalEvents
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class GlobalEvent implements Event
{

    const ILIAS_COMPONENT = 'global';


    /**
     * @return String
     */
    public function getILIASComponent() : String
    {
        return self::ILIAS_COMPONENT;
    }
}