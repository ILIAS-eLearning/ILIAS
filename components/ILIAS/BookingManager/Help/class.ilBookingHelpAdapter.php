<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Help adapter for booking manager
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingHelpAdapter
{
    protected ilObjBookingPool $pool;
    protected ilHelpGUI $help;

    public function __construct(
        ilObjBookingPool $pool,
        ilHelpGUI $help
    ) {
        $this->pool = $pool;
        $this->help = $help;
    }

    public function setHelpId(string $a_id): void
    {
        $ilHelp = $this->help;

        $object_subtype = ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE)
            ? '-schedule'
            : '-nonschedule';

        $ilHelp->setScreenIdComponent('book');
        $ilHelp->setScreenId('object' . $object_subtype);
        $ilHelp->setSubScreenId($a_id);
    }
}
