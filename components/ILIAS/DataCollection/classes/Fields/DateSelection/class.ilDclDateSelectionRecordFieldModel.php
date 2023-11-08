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

class ilDclDateSelectionRecordFieldModel extends ilDclSelectionRecordFieldModel
{
    public const PROP_SELECTION_TYPE = 'date_selection_type';
    public const PROP_SELECTION_OPTIONS = 'date_selection_options';

    public function parseExportValue($value): string
    {
        $dates = [];
        foreach (ilDclSelectionOption::getValues((int)$this->getField()->getId(), $value) as $value) {
            $date = new ilDate($value, IL_CAL_DATE);
            $dates[] = $date->get(IL_CAL_DATE);
        }
        return implode("; ", $dates);
    }
}
