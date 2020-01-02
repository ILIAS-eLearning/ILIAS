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

include_once('./Services/Calendar/classes/iCal/class.ilICalItem.php');


/**
* Represents a ical component.
* E.g. VEVENT
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilICalComponent extends ilICalItem
{
    /**
     * Constructor
     *
     * @access public
     * @param string name of component
     */
    public function __construct($a_name)
    {
        parent::__construct($a_name);
    }
    
    /**
     * get items by name
     *
     * @access public
     * @param string name
     *
     */
    public function getItemsByName($a_name, $a_recursive = true)
    {
        foreach ($this->getItems() as $item) {
            if ($item->getName() == $a_name) {
                $found[] = $item;
            }
            if ($a_recursive) {
                foreach ($item->getItemsByName($a_name) as $hit) {
                    $found[] = $hit;
                }
            }
        }
        return $found ? $found : array();
    }
}
