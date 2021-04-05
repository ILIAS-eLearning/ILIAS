<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilFormSubmitRecursiveSlashesStripper implements ilFormValuesManipulator
{
    /**
     * @param array $inputValues
     * @return array
     */
    public function manipulateFormInputValues($inputValues)
    {
        return $inputValues;
    }
    
    /**
     * @param array $submitValues
     * @return array|mixed|string
     * @throws ilFormException
     */
    public function manipulateFormSubmitValues($submitValues)
    {
        foreach ($submitValues as $identifier => $value) {
            if (is_object($value)) {
                // post submit does not support objects, so when
                // object building happened, sanitizing did also
                continue;
            }
            
            $submitValues[$identifier] = ilUtil::stripSlashesRecursive($value);
        }
        
        return $submitValues;
    }
}
