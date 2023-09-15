<?php

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
