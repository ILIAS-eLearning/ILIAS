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
