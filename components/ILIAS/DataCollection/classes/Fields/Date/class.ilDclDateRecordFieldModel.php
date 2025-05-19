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

class ilDclDateRecordFieldModel extends ilDclBaseRecordFieldModel
{
    /**
     * @param int|string|null $value
     */
    public function parseValue($value): string
    {
        return (string) $value;
    }

    public function getValueFromExcel(ilExcel $excel, int $row, int $col): ?string
    {
        $value = parent::getValueFromExcel($excel, $row, $col);

        if ($value) {
            return date('Y-m-d', strtotime($value));
        } else {
            return "";
        }
    }

    /**
     * @param string $value
     */
    public function parseExportValue($value): ?string
    {
        return (new ilDate($value, IL_CAL_DATE))->get(IL_CAL_DATE);
    }

    /**
     * @param string $value
     */
    public function parseSortingValue($value, bool $link = true): ?int
    {
        return strtotime($value);
    }

    public function getFormulaValue(): string
    {
        return (string) strtotime($this->getValue() ?: '');
    }
}
