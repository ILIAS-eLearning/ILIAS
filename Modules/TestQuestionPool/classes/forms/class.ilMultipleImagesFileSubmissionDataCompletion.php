<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilMultipleImagesFileSubmissionDataCompletion implements ilFormValuesManipulator
{
    protected $postVar;
    
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
        return $inputValues;
    }
    
    public function manipulateFormSubmitValues(array $submitValues) : array
    {
        global $DIC;

        $_REQUEST[$this->getPostVar()] = $this->populateStoredFileCustomUploadProperty(
            $_REQUEST[$this->getPostVar()],
            $submitValues
        );

        return $submitValues;
    }
    
    protected function populateStoredFileCustomUploadProperty($submitFiles, $submitValues)
    {
        $submitFiles['dodging_file'] = array();
        
        foreach ($submitValues as $identifier => $storedFilename) {
            $submitFiles['dodging_file'][$identifier] = $storedFilename;
        }
        
        return $submitFiles;
    }
}
