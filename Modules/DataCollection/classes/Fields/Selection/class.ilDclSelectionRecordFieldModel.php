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

abstract class ilDclSelectionRecordFieldModel extends ilDclBaseRecordFieldModel
{
    /**
     * @return array|string
     */
    public function getValue()
    {
        if ($this->getField()->isMulti() && !is_array($this->value)) {
            return [$this->value];
        }
        if (!$this->getField()->isMulti() && is_array($this->value)) {
            return array_shift($this->value);
        }

        return $this->value;
    }

    /**
     * @param array|string|int $value
     */
    public function parseExportValue($value): string
    {
        $values = ilDclSelectionOption::getValues((int) $this->getField()->getId(), $value);

        return implode("; ", $values);
    }

    public function getValueFromExcel(ilExcel $excel, int $row, int $col)
    {
        $string = parent::getValueFromExcel($excel, $row, $col);
        $old = $string;
        if ($this->getField()->isMulti()) {
            $string = $this->getMultipleValuesFromString($string);
            $has_value = count($string);
        } else {
            $string = $this->getValueFromString($string);
            $has_value = $string;
        }

        if (!$has_value && $old) {
            $warning = "(" . $row . ", " . ilDataCollectionImporter::getExcelCharForInteger($col + 1) . ") " . $this->lng->txt("dcl_no_such_reference") . " "
                . $old;

            return ['warning' => $warning];
        }

        return $string;
    }

    /**
     * Copied from reference field and slightly adjusted.
     * This method tries to get as many valid values out of a string separated by commata. This is problematic as a string value could contain commata itself.
     * It is optimized to work with an exported list from this DataCollection. And works fine in most cases. Only areference list with the values "hello" and "hello, world"
     * Will mess with it.
     * @param $stringValues string
     * @return int[]
     */
    protected function getMultipleValuesFromString(string $stringValues): array
    {
        $delimiter = strpos($stringValues, '; ') ? '; ' : ', ';
        $slicedStrings = explode($delimiter, $stringValues);
        $slicedReferences = [];
        $resolved = 0;
        for ($i = 0; $i < count($slicedStrings); $i++) {
            $searchString = implode(array_slice($slicedStrings, $resolved, $i - $resolved + 1));
            if ($ref = $this->getValueFromString($searchString)) {
                $slicedReferences[] = $ref;
                $resolved = $i;
                continue;
            }

            $searchString = $slicedStrings[$i];
            if ($ref = $this->getValueFromString($searchString)) {
                $slicedReferences[] = $ref;
                $resolved = $i;
            }
        }

        return $slicedReferences;
    }

    protected function getValueFromString(string $string): ?int
    {
        foreach ($this->getField()->getProperty($this->field::PROP_SELECTION_OPTIONS) as $id => $value) {
            if ($value == $string) {
                return $id;
            }
        }

        return null;
    }
}
