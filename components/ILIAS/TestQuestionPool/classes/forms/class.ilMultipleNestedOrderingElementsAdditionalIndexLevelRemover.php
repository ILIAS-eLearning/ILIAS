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
class ilMultipleNestedOrderingElementsAdditionalIndexLevelRemover implements ilFormValuesManipulator
{
    public function manipulateFormInputValues(array $inputValues): array
    {
        return $inputValues;
    }

    public function manipulateFormSubmitValues(array $submitValues): array
    {
        return $this->fetchIndentationsFromSubmitValues($submitValues);
    }

    protected function hasContentSubLevel($values): bool
    {
        if (!is_array($values) || !isset($values['content'])) {
            return false;
        }

        return true;
    }

    protected function hasIndentationsSubLevel($values): bool
    {
        if (!is_array($values) || !isset($values['indentation'])) {
            return false;
        }

        return true;
    }

    protected function fetchIndentationsFromSubmitValues($values): array
    {
        if ($this->hasContentSubLevel($values) && $this->hasIndentationsSubLevel($values)) {
            $actualValues = array();

            foreach ($values['content'] as $key => $value) {
                if (!isset($values['indentation'][$key])) {
                    $actualValues[$key] = null;
                    continue;
                }

                $actualValues[$key] = $values['indentation'][$key];
            }
        } else {
            $actualValues = $values;
        }

        return $actualValues;
    }
}
