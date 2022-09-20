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

include_once './webservice/soap/classes/class.ilXMLResultSetColumn.php';
include_once './webservice/soap/classes/class.ilXMLResultSetRow.php';

/**
 * Class to handle XML ResultSets
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @version $Id: class.ilXMLResultSet.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
 * @package ilias
 */
class ilXMLResultSet
{
    private array $colspecs = [];
    private array $rows = [];

    public function getColumnName(int $index): ?string
    {
        if ($index < 0 || $index > count($this->colspecs)) {
            return null;
        }
        return $this->colspecs[$index] instanceof ilXMLResultSetColumn ? $this->colspecs[$index]->getName() : null;
    }

    /**
     * create a new column with columnname and attach it to column list
     */
    public function addColumn(string $columnname): void
    {
        $this->colspecs[] = new ilXMLResultSetColumn(count($this->colspecs), $columnname);
    }

    /**
     * return index for column name
     */
    public function getIndexForColumn(string $columnname): int
    {
        $idx = 0;
        foreach ($this->colspecs as $colspec) {
            if (strcasecmp($columnname, $colspec->getName()) === 0) {
                return $idx;
            }
            $idx++;
        }
        return -1;
    }

    /**
     * has column name
     */
    public function hasColumn(string $columnname): bool
    {
        return $this->getIndexForColumn($columnname) !== -1;
    }

    /**
     * return array of ilXMLResultSetColumn
     * @return ilXMLResultSetColumn[]
     */
    public function getColSpecs(): array
    {
        return $this->colspecs;
    }

    /**
     * return array of ilXMLResultSetRow
     * @return ilXMLResultSetRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function addRow(ilXMLResultSetRow $row): void
    {
        $this->rows[] = $row;
    }

    /**
     * Clear table value and sets them based on array. Exspects a 2-dimension array. Column indeces of second dimensions in first row are column names.
     * e.g. array (array("first" => "val1_1", "second" => "val1_2), array ("first" => "val2_1", "second" => "val2_2"))
     * results in Table   first       second
     *                    val1_1      va11_2
     *                    val2_1      val2_2
     */
    public function setArray(array $array): void
    {
        $this->addArray($array, true);
    }

    /**
     * Add table values. Exspects a 2-dimension array. Column indeces of second dimensions in first row are column names.
     * e.g. array (array("first" => "val1_1", "second" => "val1_2), array ("first" => "val2_1", "second" => "val2_2"))
     * results in Table   first       second
     *                    val1_1      va11_2
     *                    val2_1      val2_2
     * @param array $array     2 dimensional array
     * @param bool  $overwrite if false, column names won't be changed, rows will be added,true: result set will be reset to null and data will be added.
     */
    public function addArray(array $array, bool $overwrite = false): void
    {
        if ($overwrite) {
            $this->clear();
        }
        foreach ($array as $row) {
            if ($overwrite) {
                // add column names from first row
                $columnNames = array_keys($row);
                foreach ($columnNames as $columnName) {
                    $this->addColumn($columnName);
                }
                $overwrite = false;
            }
            $xmlRow = new ilXMLResultSetRow();
            $xmlRow->setValues($row);
            $this->addRow($xmlRow);
        }
    }

    public function clear(): void
    {
        $this->rows = array();
        $this->colspecs = array();
    }

    public function getColumnCount(): int
    {
        return count($this->colspecs);
    }

    public function getRowCount(): int
    {
        return count($this->rows);
    }

    /**
     * return row for index idx
     */
    public function getRow($idx): ilXMLResultSetRow
    {
        if ($idx < 0 || $idx >= $this->getRowCount()) {
            throw new DomainException("Index too small or too big: " . $idx);
        }
        return $this->rows[$idx];
    }

    /**
     * return column value at colidx and rowidx
     * @param int        $rowIdx
     * @param int|string $colIdx
     * @return string
     */
    public function getValue(int $rowIdx, $colIdx): string
    {
        $row = $this->getRow($rowIdx);

        if (!is_numeric($colIdx)) {
            $colIdx = $this->getIndexForColumn($colIdx);
        }
        return $row->getValue($colIdx);
    }
}
