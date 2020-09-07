<?php
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
* @version $Id$
*
* @ingroup ServicesCalendar
*/
interface ilCalendarRecurrenceCalculation
{
    /**
     * Get Frequence type of recurrence
     */
    public function getFrequenceType();

    /**
     * Get timezone of recurrence
     */
    public function getTimeZone();

    /**
     * Get number of recurrences
     */
    public function getFrequenceUntilCount();


    /**
     * Get end data of recurrence
     */
    public function getFrequenceUntilDate();

    /**
     * Get interval of recurrence
     */
    public function getInterval();

    /**
     * Get BYMONTHList
     */
    public function getBYMONTHList();

    /**
     * Get BYWEEKNOList
     */
    public function getBYWEEKNOList();

    /**
     * Get BYYEARDAYLIST
     */
    public function getBYYEARDAYList();

    /**
     * GEt BYMONTHDAY List
     */
    public function getBYMONTHDAYList();


    /**
     * Get BYDAY List
     */
    public function getBYDAYList();

    /**
     * Get BYSETPOS List
     */
    public function getBYSETPOSList();

    /**
     * Get exclusion dates
     */
    public function getExclusionDates();


    /**
     * validate recurrence
     */
    public function validate();
}
