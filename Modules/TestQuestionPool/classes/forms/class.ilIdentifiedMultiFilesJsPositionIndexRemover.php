<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilIdentifiedMultiFilesJsPositionIndexRemover extends ilIdentifiedMultiValuesJsPositionIndexRemover
{
    protected $postVar = null;
    
    public function getPostVar()
    {
        return $this->postVar;
    }
    
    public function setPostVar($postVar) : void
    {
        $this->postVar = $postVar;
    }
    
    public function manipulateFormInputValues(array $inputValues) : array
    {
        // KEEP THIS INTERFACE METHOD OVERWRITTEN THIS LIKE (!)
        return $inputValues;
    }

    public function manipulateFormSubmitValues(array $values) : array
    {
        if ($this->isFileSubmitAvailable()) {
            $this->prepareFileSubmit();
        }
        
        return $values;
    }
    
    protected function isFileSubmitAvailable() : bool
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
    
    protected function prepareFileSubmit() : void
    {
        $_FILES[$this->getPostVar()] = $this->prepareMultiFilesSubmitValues(
            $_FILES[$this->getPostVar()]
        );
    }
    
    protected function prepareMultiFilesSubmitValues($filesSubmitValues) : array
    {
        return $this->removePositionIndexLevels($filesSubmitValues);
    }
}
