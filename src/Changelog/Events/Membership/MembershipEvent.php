<?php

namespace ILIAS\Changelog\Events\Membership;

use ILIAS\Changelog\Interfaces\Event;

/**
 * Class MembershipEvent
 *
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class MembershipEvent implements Event
{

    const ILIAS_COMPONENT = 'Services/Membership';


    /**
     * @return String
     */
    public function getILIASComponent() : String
    {
        return self::ILIAS_COMPONENT;
    }
}