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
 * Abstract base class for all ical items (Component, Parameter and Value)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
abstract class ilICalItem
{
    protected string $name = '';
    protected string $value = '';
    protected array $items = [];

    public function __construct(string $a_name, string $a_value = '')
    {
        $this->name = $a_name;
        $this->value = $a_value;
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : string
    {
        return trim($this->value);
    }

    public function getItems() : array
    {
        return $this->items;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getItemsByName(string $a_name, bool $a_recursive = true) : array
    {
        return [];
    }

    public function addItem(ilICalItem $a_item) : void
    {
        $this->items[] = $a_item;
    }
}
