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
 * Class ilCalendarConstants
 *
 * @author Stefan Meyer <meyer@leifos.de>
 */
class ilCalendarConstants
{
    /**
     * @see ilDateTime->increment, ilDateTime::_before,...
     */
    public const YEAR = 'year';
    public const MONTH = 'month';
    public const WEEK = 'week';
    public const DAY = 'day';
    public const HOUR = 'hour';
    public const MINUTE = 'minute';
    public const SECOND = 'second';

    public const DATETIME = 1;
    public const DATE = 2;
    public const UNIX = 3;
    public const FKT_DATE = 4;
    public const FKT_GETDATE = 5;
    public const TIMESTAMP = 6;
    public const ISO_8601 = 7;
}
