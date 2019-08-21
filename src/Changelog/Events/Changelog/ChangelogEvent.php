<?php

namespace ILIAS\Changelog\Events\Changelog;

use ILIAS\Changelog\Interfaces\Event;

/**
 * Class GlobalEvent
 *
 * @package ILIAS\Changelog\Events\GlobalEvents
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ChangelogEvent implements Event
{

    const ILIAS_COMPONENT = 'Services/Changelog';


    /**
     * @return String
     */
    public function getILIASComponent() : String
    {
        return self::ILIAS_COMPONENT;
    }
}