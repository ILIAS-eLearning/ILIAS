<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilIdentifiedMultiValuesJsPositionIndexRemover implements ilFormValuesManipulator
{
    const IDENTIFIER_INDICATOR_PREFIX = 'IDENTIFIER~';
    
    public function manipulateFormInputValues($inputValues)
    {
        return $this->brandIdentifiersWithIndicator($inputValues);
    }
    
    protected function brandIdentifiersWithIndicator($origValues)
    {
        $brandedValues = array();
        
        foreach ($origValues as $identifier => $val) {
            $brandedValues[$this->getIndicatorBrandedIdentifier($identifier)] = $val;
        }

        return $brandedValues;
    }
    
    protected function getIndicatorBrandedIdentifier($identifier)
    {
        return self::IDENTIFIER_INDICATOR_PREFIX . $identifier;
    }
    
    public function manipulateFormSubmitValues($submitValues)
    {
        $_POST['cmd'] = $this->cleanSubmitCommandFromPossibleIdentifierIndicators($_POST['cmd']);
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
    
    protected function removePositionIndexLevels($values)
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
    
    protected function isPositionIndexLevel($val)
    {
        if (!is_array($val)) {
            return false;
        }
        
        if (count($val) != 1) {
            return false;
        }
        
        return true;
    }
    
    protected function isValueIdentifier($key)
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
