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
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once('./Services/Calendar/classes/class.ilTimeZone.php');

/**
* Class for single dates.
* ilDate('2008-03-15') is nothing else than ilDateTime('2008-03-15',IL_CAL_DATE,'UTC')
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilDate extends ilDateTime
{
    
    /**
     * Create new date object
     *
     * @access public
     * @param mixed integer string following the format given as the second parameter
     * @param int format of date presentation
     *
     */
    public function __construct($a_date = '', $a_format = 0)
    {
        parent::__construct($a_date, $a_format, ilTimeZone::UTC);
        
        $this->default_timezone = ilTimeZone::_getInstance('UTC');
    }
    
    /**
     * get formatted date
     *
     * @access public
     * @param int format type
     * @param string format string
     */
    public function get($a_format, $a_format_str = '', $a_tz = '')
    {
        return parent::get($a_format, $a_format_str);
    }

    /**
     * To string for dates
     */
    public function __toString()
    {
        return $this->get(IL_CAL_DATE) . '<br />';
    }
}
