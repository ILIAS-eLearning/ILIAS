<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilIdentifiedMultiValuesJsPositionIndexRemover implements ilFormValuesManipulator
{
    public const IDENTIFIER_INDICATOR_PREFIX = 'IDENTIFIER~';

    public function manipulateFormInputValues(array $inputValues): array
    {
        return $this->brandIdentifiersWithIndicator($inputValues);
    }

    protected function brandIdentifiersWithIndicator(array $origValues): array
    {
        $brandedValues = array();

        foreach ($origValues as $identifier => $val) {
            $brandedValues[$this->getIndicatorBrandedIdentifier($identifier)] = $val;
        }

        return $brandedValues;
    }

    protected function getIndicatorBrandedIdentifier(string $identifier): string
    {
        return self::IDENTIFIER_INDICATOR_PREFIX . $identifier;
    }

    public function manipulateFormSubmitValues(array $submitValues): array
    {
        //$_POST['cmd'] = $this->cleanSubmitCommandFromPossibleIdentifierIndicators($_POST['cmd']);
        return $this->removePositionIndexLevels($submitValues);
    }

    protected function cleanSubmitCommandFromPossibleIdentifierIndicators($cmdArrayLevel)
    {
        if (is_array($cmdArrayLevel)) {
            $currentKey = key($cmdArrayLevel);
            $nextLevel = current($cmdArrayLevel);

            $nextLevel = $this->cleanSubmitCommandFromPossibleIdentifierIndicators($nextLevel);

            unset($cmdArrayLevel[$currentKey]);

            if ($this->isValueIdentifier($currentKey)) {
                $currentKey = $this->removeIdentifierIndicator($currentKey);
            }

            $cmdArrayLevel[$currentKey] = $nextLevel;
        }

        return $cmdArrayLevel;
    }

    protected function removePositionIndexLevels(array $values): array
    {
        foreach ($values as $key => $val) {
            unset($values[$key]);

            if ($this->isValueIdentifier($key)) {
                $key = $this->removeIdentifierIndicator($key);

                if ($this->isPositionIndexLevel($val)) {
                    $val = $this->fetchPositionIndexedValue($val);
                }
            } elseif (is_array($val)) {
                $val = $this->removePositionIndexLevels($val);
            }

            $values[$key] = $val;
        }

        return $values;
    }

    protected function isPositionIndexLevel($val): bool
    {
        if (!is_array($val)) {
            return false;
        }

        if (count($val) != 1) {
            return false;
        }

        return true;
    }

    protected function isValueIdentifier($key): bool
    {
        $indicatorPrefixLength = self::IDENTIFIER_INDICATOR_PREFIX;

        if (strlen($key) <= strlen($indicatorPrefixLength)) {
            return false;
        }

        if (substr($key, 0, strlen($indicatorPrefixLength)) != $indicatorPrefixLength) {
            return false;
        }

        return true;
    }

    protected function removeIdentifierIndicator($key)
    {
        return str_replace(self::IDENTIFIER_INDICATOR_PREFIX, '', $key);
    }

    protected function fetchPositionIndexedValue($value)
    {
        return current($value);
    }
}
