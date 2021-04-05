<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilMultiFilesSubmitRecursiveSlashesStripper implements ilFormValuesManipulator
{
    /**
     * @var string
     */
    protected $postVar = null;
    
    /**
     * @return string
     */
    public function getPostVar()
    {
        return $this->postVar;
    }
    
    /**
     * @param string $postVar
     */
    public function setPostVar($postVar)
    {
        $this->postVar = $postVar;
    }
    
    /**
     * @param array $inputValues
     * @return array $inputValues
     */
    public function manipulateFormInputValues($inputValues)
    {
        return $inputValues;
    }
    
    /**
     * @param array $submitValues
     * @return array $submitValues
     */
    public function manipulateFormSubmitValues($submitValues)
    {
        $this->manipulateFileSubmitValues();
        return $submitValues;
    }
    
    /**
     * perform the strip slashing on files submit
     */
    protected function manipulateFileSubmitValues()
    {
        if ($_FILES) {
            $_FILES[$this->getPostVar()] = ilUtil::stripSlashesRecursive(
                $_FILES[$this->getPostVar()]
            );
        }
    }
}
