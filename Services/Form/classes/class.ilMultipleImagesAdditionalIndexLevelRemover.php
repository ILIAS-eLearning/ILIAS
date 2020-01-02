<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilMultipleImagesAdditionalIndexLevelRemover implements ilFormValuesManipulator
{
    protected $filesSubmissionProperties = array(
        'name', 'tmp_name', 'type', 'error', 'size'
    );
    
    protected $postVar;
    
    public function getPostVar()
    {
        return $this->postVar;
    }
    
    public function setPostVar($postVar)
    {
        $this->postVar = $postVar;
    }

    protected function getFilesSubmissionProperties()
    {
        return $this->filesSubmissionProperties;
    }
    public function manipulateFormInputValues($inputValues)
    {
        return $inputValues;
    }
    
    public function manipulateFormSubmitValues($submitValues)
    {
        $submitValues = $this->removeAdditionalSubFieldsLevelFromSubmitValues($submitValues);

        if ($_FILES) {
            $_FILES[$this->getPostVar()] = $this->removeAdditionalSubFieldsLevelFromFilesSubmit(
                $_FILES[$this->getPostVar()]
            );
        }

        return $submitValues;
    }
    protected function isSubFieldAvailable($values, $subFieldName)
    {
        if (!is_array($values)) {
            return false;
        }
        
        if (!isset($values[$subFieldName])) {
            return false;
        }
        
        if (!is_array($values[$subFieldName])) {
            return false;
        }
        
        return true;
    }
    
    protected function isIteratorSubfieldAvailable($values)
    {
        return $this->isSubFieldAvailable($values, ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME);
    }
    
    protected function isUploadSubfieldAvailable($values)
    {
        return $this->isSubFieldAvailable($values, ilMultipleImagesInputGUI::IMAGE_UPLOAD_SUBFIELD_NAME);
    }
    
    protected function removeAdditionalSubFieldsLevelFromSubmitValues($values)
    {
        if (!$this->isIteratorSubfieldAvailable($values)) {
            return $values;
        }
        
        $storedImages = $values[ilMultipleImagesInputGUI::STORED_IMAGE_SUBFIELD_NAME];
        $actualValues = array();
            
        foreach ($values[ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME] as $index => $value) {
            if (!isset($storedImages[$index])) {
                $actualValues[$index] = '';
                continue;
            }
            
            $actualValues[$index] = $storedImages[$index];
        }
        
        return $actualValues;
    }
    
    protected function removeAdditionalSubFieldsLevelFromFilesSubmitProperty($uploadProperty)
    {
        if (!$this->isUploadSubfieldAvailable($uploadProperty)) {
            return $uploadProperty;
        }
        
        foreach ($uploadProperty as $subField => $submittedFile) {
            foreach ($submittedFile as $identifier => $uploadValue) {
                $uploadProperty[$identifier] = $uploadValue;
            }
            
            unset($uploadProperty[$subField]);
        }
        
        return $uploadProperty;
    }
    
    protected function removeAdditionalSubFieldsLevelFromFilesSubmit($filesSubmit)
    {
        foreach ($this->getFilesSubmissionProperties() as $uploadProperty) {
            if (!isset($filesSubmit[$uploadProperty])) {
                continue;
            }

            $filesSubmit[$uploadProperty] = $this->removeAdditionalSubFieldsLevelFromFilesSubmitProperty(
                $filesSubmit[$uploadProperty]
            );
        }
        
        return $filesSubmit;
    }
}
