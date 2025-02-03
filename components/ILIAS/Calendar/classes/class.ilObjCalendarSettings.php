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
 * @defgroup ServicesCalendar Services/Calendar
 * @author   Stefan Meyer <meyer@leifos.com>
 * @version  $Id$
 * @ingroup  ServicesCalendar
 */
class ilObjCalendarSettings extends ilObject
{
    /**
     * @inheritDoc
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "cals";
        parent::__construct($a_id, $a_call_by_reference);
    }
} // END class.ilObjCalendarSettings
