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

class ilDclDatetimeSelectionFieldModel extends ilDclSelectionFieldModel
{
    public const PROP_SELECTION_TYPE = 'datetime_selection_type';
    public const PROP_SELECTION_OPTIONS = 'datetime_selection_options';

    public function sanitizeOptionValue(string $value): string
    {
        return (new ilDateTime(strtotime($value), IL_CAL_UNIX))->get(IL_CAL_FKT_DATE, ilDclDatetimeFieldModel::FORMAT);
    }

    public function personalizeOptionValue(string $value, ilObjUser $user): string
    {
        $value = parent::personalizeOptionValue($value, $user);
        return (strtotime($value) === false) ? $value : date($user->getDateTimeFormat()->toString(), strtotime($value));
    }
}
