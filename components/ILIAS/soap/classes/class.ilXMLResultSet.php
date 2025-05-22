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
