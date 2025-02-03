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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ingroup ServicesCalendar
*/
interface ilCalendarRecurrenceCalculation
{
    /**
     * Get Frequence type of recurrence
     */
    public function getFrequenceType(): string;

    /**
     * Get timezone of recurrence
     */
    public function getTimeZone(): string;

    /**
     * Get number of recurrences
     */
    public function getFrequenceUntilCount(): int;


    /**
     * Get end data of recurrence
     */
    public function getFrequenceUntilDate(): ?ilDate;

    /**
     * Get interval of recurrence
     */
    public function getInterval(): int;

    /**
     * Get BYMONTHList
     * @return int[] array of "by month" items: [1,12]
     */
    public function getBYMONTHList(): array;

    /**
     * Get BYWEEKNOList
     * @return int[] array of "by week no" items: [1,54]
     */
    public function getBYWEEKNOList(): array;

    /**
     * Get BYYEARDAYLIST
     * @return int[] array of "year day" items [1,365]
     */
    public function getBYYEARDAYList(): array;

    /**
     * Get BYMONTHDAY List
     * @return int[] array of "month day" items [1,31]
     */
    public function getBYMONTHDAYList(): array;


    /**
     * Get BYDAY List
     * @return string[] array of "by month day" items: ['MO','TU']
     */
    public function getBYDAYList(): array;

    /**
     * Get BYSETPOS List
     */
    public function getBYSETPOSList(): array;

    /**
     * Get exclusion date object
     * @return ilCalendarRecurrenceExclusion[]
     */
    public function getExclusionDates(): array;


    /**
     * validate recurrence
     */
    public function validate(): bool;
}
