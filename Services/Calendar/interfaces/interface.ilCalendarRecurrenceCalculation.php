<?php declare(strict_types=1);
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

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
    public function getFrequenceType() : string;

    /**
     * Get timezone of recurrence
     */
    public function getTimeZone() : string;

    /**
     * Get number of recurrences
     */
    public function getFrequenceUntilCount() : int;


    /**
     * Get end data of recurrence
     */
    public function getFrequenceUntilDate() : ?ilDate;

    /**
     * Get interval of recurrence
     */
    public function getInterval() : int;

    /**
     * Get BYMONTHList
     * @return int[] array of "by month" items: [1,12]
     */
    public function getBYMONTHList() : array;

    /**
     * Get BYWEEKNOList
     * @return int[] array of "by week no" items: [1,54]
     */
    public function getBYWEEKNOList() : array;

    /**
     * Get BYYEARDAYLIST
     * @return int[] array of "year day" items [1,365]
     */
    public function getBYYEARDAYList() : array;

    /**
     * Get BYMONTHDAY List
     * @return int[] array of "month day" items [1,31]
     */
    public function getBYMONTHDAYList() : array;


    /**
     * Get BYDAY List
     * @return string[] array of "by month day" items: ['MO','TU']
     */
    public function getBYDAYList() : array;

    /**
     * Get BYSETPOS List
     */
    public function getBYSETPOSList() : array;

    /**
     * Get exclusion date object
     * @return ilCalendarRecurrenceExclusion[]
     */
    public function getExclusionDates() : array;


    /**
     * validate recurrence
     */
    public function validate() : bool;
}
