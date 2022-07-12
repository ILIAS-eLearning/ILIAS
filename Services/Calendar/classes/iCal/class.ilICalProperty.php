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
 * Represents a ical property.
 * E.g DTSTART;VALUE=DATE;TZID=Europe/Berlin:20080214
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilICalProperty extends ilICalItem
{
    /**
     * @inheritDoc
     */
    public function __construct(string $a_name, string $a_value = '')
    {
        parent::__construct($a_name, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function getItemsByName(string $a_name, bool $a_recursive = true) : array
    {
        $found = [];
        foreach ($this->getItems() as $item) {
            if ($item->getName() == $a_name) {
                $found[] = $item;
            }
        }
        return $found;
    }
}
