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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilFormSubmitRecursiveSlashesStripper implements ilFormValuesManipulator
{
    public function manipulateFormInputValues(array $inputValues): array
    {
        return $inputValues;
    }

    public function manipulateFormSubmitValues(array $submitValues): array
    {
        foreach ($submitValues as $identifier => $value) {
            if (is_object($value)) {
                // post submit does not support objects, so when
                // object building happened, sanitizing did also
                continue;
            }

            $submitValues[$identifier] = ilArrayUtil::stripSlashesRecursive($value);
        }

        return $submitValues;
    }
}
