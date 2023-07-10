<?php

declare(strict_types=1);

/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
 * Row Class for XMLResultSet
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 */
class ilXMLResultSetRow
{
    /** @var array<int|string, string> */
    private array $columns = [];

    /**
     * set column value
     * @param int|string $index
     * @param string
     * @return void
     */
    public function setValue($index, string $value): void
    {
        $this->columns[$index] = $value;
    }

    /**
     * @return array<int|string, string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set values from array
     */
    public function setValues(array $values): void
    {
        $i = 0;
        foreach ($values as $value) {
            $this->setValue($i++, (string) $value);
        }
    }

    /**
     * Return value for column with specified index
     * @param int|string $idx
     * @return string
     */
    public function getValue($idx): string
    {
        if (is_string($idx) && !array_key_exists($idx, $this->columns)) {
            throw new DomainException('Invalid index given: ' . $idx);
        }

        if (is_int($idx) &&
            ($idx < 0 || $idx >= count($this->columns))) {
            throw new DomainException("Index too small or too large: " . $idx);
        }

        return $this->columns[$idx];
    }
}
