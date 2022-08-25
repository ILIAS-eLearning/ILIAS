<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
