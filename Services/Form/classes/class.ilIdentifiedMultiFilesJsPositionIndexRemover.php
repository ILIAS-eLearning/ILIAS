<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilIdentifiedMultiValuesJsPositionIndexRemover.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilIdentifiedMultiFilesJsPositionIndexRemover extends ilIdentifiedMultiValuesJsPositionIndexRemover
{
    protected $postVar = null;
    
    public function getPostVar()
    {
        return $this->postVar;
    }
    
    public function setPostVar($postVar)
    {
        $this->postVar = $postVar;
    }
    
    public function manipulateFormInputValues($inputValues)
    {
        // KEEP THIS INTERFACE METHOD OVERWRITTEN THIS LIKE (!)
        return $inputValues;
    }

    public function manipulateFormSubmitValues($values)
    {
        if ($this->isFileSubmitAvailable()) {
            $this->prepareFileSubmit();
        }
        
        return $values;
    }
    
    protected function isFileSubmitAvailable()
    {
        if (!isset($_FILES[$this->getPostVar()])) {
            return false;
        }
        
        if (!is_array($_FILES[$this->getPostVar()])) {
            return false;
        }
        
        if (!in_array('tmp_name', array_keys($_FILES[$this->getPostVar()]))) {
            return false;
        }
        
        return true;
    }
    
    protected function prepareFileSubmit()
    {
        $_FILES[$this->getPostVar()] = $this->prepareMultiFilesSubmitValues(
            $_FILES[$this->getPostVar()]
        );
    }
    
    protected function prepareMultiFilesSubmitValues($filesSubmitValues)
    {
        return $this->removePositionIndexLevels($filesSubmitValues);
    }
}
