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

declare(strict_types=1);
/**
 * Class for single dates.
 * ilDate('2008-03-15') is nothing else than ilDateTime('2008-03-15',IL_CAL_DATE,'UTC')
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilDate extends ilDateTime
{
    public function __construct($a_date = '', $a_format = 0)
    {
        parent::__construct($a_date, $a_format, ilTimeZone::UTC);

        $this->default_timezone = ilTimeZone::_getInstance('UTC');
    }

    public function get(int $a_format, string $a_format_str = '', string $a_tz = '')
    {
        return parent::get($a_format, $a_format_str);
    }

    /**
     * To string for dates
     */
    public function __toString(): string
    {
        return $this->get(IL_CAL_DATE) . '<br />';
    }
}
