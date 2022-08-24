<?php

declare(strict_types=1);
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
 * List of dates
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilDateList implements Iterator
{
    public const TYPE_DATE = 1;
    public const TYPE_DATETIME = 2;

    protected array $list_item = array();

    protected int $type;

    public function __construct(int $a_type)
    {
        $this->type = $a_type;
        $this->list_item = array();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        reset($this->list_item);
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return current($this->list_item);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return key($this->list_item);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        return next($this->list_item);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     */
    public function get(): array
    {
        return $this->list_item ?: array();
    }

    /**
     * get item at specific position
     */
    public function getAtPosition(int $a_pos): ?ilDateTime
    {
        $counter = 1;
        foreach ($this->get() as $item) {
            if ($counter++ == $a_pos) {
                return $item;
            }
        }
        return null;
    }

    /**
     * add a date to the date list
     */
    public function add(ilDateTime $date): void
    {
        $this->list_item[(string) $date->get(IL_CAL_UNIX)] = clone $date;
    }

    /**
     * Merge two lists
     */
    public function merge(ilDateList $other_list): void
    {
        foreach ($other_list->get() as $new_date) {
            $this->add($new_date);
        }
    }

    /**
     * remove from list
     */
    public function remove(ilDateTime $remove): void
    {
        $unix_remove = $remove->get(IL_CAL_UNIX);
        if (isset($this->list_item[$unix_remove])) {
            unset($this->list_item[$unix_remove]);
        }
    }

    public function removeByDAY(ilDateTime $remove): void
    {
        foreach ($this->list_item as $key => $dt) {
            if (ilDateTime::_equals($remove, $dt, IL_CAL_DAY, ilTimeZone::UTC)) {
                unset($this->list_item[$key]);
            }
        }
    }

    /**
     * Sort list
     */
    public function sort(): void
    {
        ksort($this->list_item, SORT_NUMERIC);
    }

    public function __toString(): string
    {
        $out = '<br />';
        foreach ($this->get() as $date) {
            $out .= $date->get(IL_CAL_DATETIME, '', 'Europe/Berlin') . '<br/>';
        }
        return $out;
    }
}
