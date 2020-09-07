<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Services/Form
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
