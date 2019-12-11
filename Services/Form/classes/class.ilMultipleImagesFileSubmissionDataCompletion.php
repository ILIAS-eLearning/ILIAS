<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilMultipleImagesFileSubmissionDataCompletion implements ilFormValuesManipulator
{
    protected $postVar;
    
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
        return $inputValues;
    }
    
    public function manipulateFormSubmitValues($submitValues)
    {
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
