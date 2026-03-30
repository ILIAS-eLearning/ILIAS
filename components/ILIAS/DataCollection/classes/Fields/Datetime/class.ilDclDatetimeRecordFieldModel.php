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

class ilDclDatetimeRecordFieldModel extends ilDclBaseRecordFieldModel
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

        if ($value && strtotime($value) > strtotime('0000-00-00 00:00:00')) {
            return date(ilDclDatetimeFieldModel::FORMAT, strtotime($value));
        } else {
            return "";
        }
    }

    /**
     * This value should be UTC but the current ilDateTimeInputGUI enforces the users timezone on an input.
     * Therefore it is added before the value preservation to ensure to save the "raw" date.
     */
    public function setValueFromForm(ilPropertyFormGUI $form): void
    {
        parent::setValueFromForm($form);
        $date = new ilDateTime(strtotime($this->getValue()), IL_CAL_UNIX);
        $this->setValue($date->get(IL_CAL_FKT_DATE, ilDclDatetimeFieldModel::FORMAT, $this->user->getTimeZone()));
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


    public function getPlainText(): string
    {
        return $this->getValue() ? date($this->user->getDateTimeFormat()->toString(), strtotime($this->getValue())) : '';
    }
}
