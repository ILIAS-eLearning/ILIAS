<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingFormValuesObjectsConverter implements ilFormValuesManipulator
{
    const INDENTATIONS_POSTVAR_SUFFIX = '_ordering';
    const INDENTATIONS_POSTVAR_SUFFIX_JS = '__default';
    
    const CONTEXT_MAINTAIN_ELEMENT_TEXT = 'maintainItemText';
    const CONTEXT_MAINTAIN_ELEMENT_IMAGE = 'maintainItemImage';
    const CONTEXT_MAINTAIN_HIERARCHY = 'maintainHierarchy';
    
    /**
     * @var string
     */
    protected $context = null;
    
    /**
     * @var string
     */
    protected $postVar = null;
    
    /**
     * @var string
     */
    protected $imageRemovalCommand = null;
    
    /**
     * @var string
     */
    protected $imageUrlPath;
    
    /**
     * @var string
     */
    protected $imageFsPath;
    
    /**
     * @var string
     */
    protected $thumbnailPrefix;
    
    /**
     * @return string
     */
    public function getContext() : ?string
    {
        return $this->context;
    }
    
    /**
     * @param $context
     */
    public function setContext($context) : void
    {
        $this->context = $context;
    }
    
    /**
     * @return string
     */
    public function getPostVar() : ?string
    {
        return $this->postVar;
    }
    
    /**
     * @param $postVar
     */
    public function setPostVar($postVar) : void
    {
        $this->postVar = $postVar;
    }
    
    public function getImageRemovalCommand() : ?string
    {
        return $this->imageRemovalCommand;
    }
    
    public function setImageRemovalCommand($imageRemovalCommand) : void
    {
        $this->imageRemovalCommand = $imageRemovalCommand;
    }
    
    public function getImageUrlPath() : string
    {
        return $this->imageUrlPath;
    }
    
    /**
     * @param string $imageUrlPath
     */
    public function setImageUrlPath($imageUrlPath) : void
    {
        $this->imageUrlPath = $imageUrlPath;
    }
    
    /**
     * @return string
     */
    public function getImageFsPath() : string
    {
        return $this->imageFsPath;
    }
    
    /**
     * @param string $imageFsPath
     */
    public function setImageFsPath($imageFsPath) : void
    {
        $this->imageFsPath = $imageFsPath;
    }
    
    /**
     * @return string
     */
    public function getThumbnailPrefix() : string
    {
        return $this->thumbnailPrefix;
    }
    
    /**
     * @param string $thumbnailPrefix
     */
    public function setThumbnailPrefix($thumbnailPrefix) : void
    {
        $this->thumbnailPrefix = $thumbnailPrefix;
    }
    
    public function getIndentationsPostVar() : string
    {
        $postVar = $this->getPostVar();
        $postVar .= self::INDENTATIONS_POSTVAR_SUFFIX;
        $postVar .= self::INDENTATIONS_POSTVAR_SUFFIX_JS;
        
        return $postVar;
    }
    
    protected function needsConvertToValues($elementsOrValues) : bool
    {
        if (!count($elementsOrValues)) {
            return false;
        }
        
        return (current($elementsOrValues) instanceof ilAssOrderingElement);
    }
    
    public function manipulateFormInputValues(array $inputValues) : array
    {
        if ($this->needsConvertToValues($inputValues)) {
            $inputValues = $this->collectValuesFromElements($inputValues);
        }
        
        return $inputValues;
    }
    
    protected function collectValuesFromElements(array $elements) : array
    {
        $values = array();
        
        foreach ($elements as $identifier => $orderingElement) {
            switch ($this->getContext()) {
                case self::CONTEXT_MAINTAIN_ELEMENT_TEXT:
                    
                    $values[$identifier] = $this->getTextContentValueFromObject($orderingElement);
                    break;
                
                case self::CONTEXT_MAINTAIN_ELEMENT_IMAGE:
                    
                    $values[$identifier] = $this->getImageContentValueFromObject($orderingElement);
                    break;
                
                case self::CONTEXT_MAINTAIN_HIERARCHY:
                    
                    $values[$identifier] = $this->getStructValueFromObject($orderingElement);
                    break;
                
                default:
                    throw new ilFormException('unsupported context: ' . $this->getContext());
            }
        }
        
        return $values;
    }
    
    protected function getTextContentValueFromObject(ilAssOrderingElement $element) : ?string
    {
        return $element->getContent();
    }
    
    protected function getImageContentValueFromObject(ilAssOrderingElement $element) : array
    {
        $element->setImagePathWeb($this->getImageUrlPath());
        $element->setImagePathFs($this->getImageFsPath());
        $element->setImageThumbnailPrefix($this->getThumbnailPrefix());
        
        return array(
            'title' => $element->getContent(),
            'src' => $element->getPresentationImageUrl()
        );
    }
    
    protected function getStructValueFromObject(ilAssOrderingElement $element) : array
    {
        return array(
            'answer_id' => $element->getId(),
            'random_id' => $element->getRandomIdentifier(),
            'content' => (string) $element->getContent(),
            'ordering_position' => $element->getPosition(),
            'ordering_indentation' => $element->getIndentation()
        );
    }
    
    protected function needsConvertToElements($valuesOrElements) : bool
    {
        if (!count($valuesOrElements)) {
            return false;
        }
        
        return !(current($valuesOrElements) instanceof ilAssOrderingElement);
    }
    
    public function manipulateFormSubmitValues(array $submitValues) : array
    {
        if ($this->needsConvertToElements($submitValues)) {
            $submitValues = $this->constructElementsFromValues($submitValues);
        }
        
        return $submitValues;
    }
    
    public function constructElementsFromValues(array $values) : array
    {
        $elements = array();
        
        $position = 0;
        
        foreach ($values as $identifier => $value) {
            $element = new ilAssOrderingElement();
            $element->setRandomIdentifier($identifier);
            
            $element->setPosition($position++);
            
            if ($this->getContext() == self::CONTEXT_MAINTAIN_HIERARCHY) {
                $element->setIndentation($value);
            } else {
                $element->setContent($value);
            }
            
            if ($this->getContext() == self::CONTEXT_MAINTAIN_ELEMENT_IMAGE) {
                $element->setUploadImageName($this->fetchSubmittedImageFilename($identifier));
                $element->setUploadImageFile($this->fetchSubmittedUploadFilename($identifier));
                
                $element->setImageRemovalRequest($this->wasImageRemovalRequested($identifier));
            }
            
            $elements[$identifier] = $element;
        }
        
        return $elements;
    }
    
    protected function fetchSubmittedImageFilename($identifier)
    {
        $fileUpload = $this->fetchElementFileUpload($identifier);
        return $this->fetchSubmittedFileUploadProperty($fileUpload, 'name');
    }
    
    protected function fetchSubmittedUploadFilename($identifier)
    {
        $fileUpload = $this->fetchElementFileUpload($identifier);
        return $this->fetchSubmittedFileUploadProperty($fileUpload, 'tmp_name');
    }
    
    protected function fetchSubmittedFileUploadProperty($fileUpload, $property)
    {
        if (!isset($fileUpload[$property]) || !strlen($fileUpload[$property])) {
            return null;
        }
        
        return $fileUpload[$property];
    }
    
    protected function fetchElementFileUpload($identifier)
    {
        $uploadFiles = $this->fetchSubmittedUploadFiles();
        
        if (!isset($uploadFiles[$identifier])) {
            return array();
        }
        
        return $uploadFiles[$identifier];
    }
    
    protected function fetchSubmittedUploadFiles() : array
    {
        $submittedUploadFiles = $this->getFileSubmitDataRestructuredByIdentifiers();
        //$submittedUploadFiles = $this->getFileSubmitsHavingActualUpload($submittedUploadFiles);
        return $submittedUploadFiles;
    }
    
    protected function getFileSubmitsHavingActualUpload($submittedUploadFiles)
    {
        foreach ($submittedUploadFiles as $identifier => $uploadProperties) {
            if (!isset($uploadProperties['tmp_name'])) {
                unset($submittedUploadFiles[$identifier]);
                continue;
            }
            
            if (!strlen($uploadProperties['tmp_name'])) {
                unset($submittedUploadFiles[$identifier]);
                continue;
            }
            
            if (!is_uploaded_file($uploadProperties['tmp_name'])) {
                unset($submittedUploadFiles[$identifier]);
                continue;
            }
        }
        
        return $submittedUploadFiles;
    }
    
    /**
     * @return array
     */
    protected function getFileSubmitDataRestructuredByIdentifiers() : array
    {
        $submittedUploadFiles = array();
        
        foreach ($this->getFileSubmitData() as $uploadProperty => $valueElement) {
            foreach ($valueElement as $elementIdentifier => $uploadValue) {
                if (!isset($submittedUploadFiles[$elementIdentifier])) {
                    $submittedUploadFiles[$elementIdentifier] = array();
                }
                
                $submittedUploadFiles[$elementIdentifier][$uploadProperty] = $uploadValue;
            }
        }
        
        return $submittedUploadFiles;
    }
    
    protected function getFileSubmitData()
    {
        if (!isset($_FILES[$this->getPostVar()])) {
            return array();
        }
        
        return $_FILES[$this->getPostVar()];
    }

    /**
     * TODO: Instead of accessing post, the complete ilFormValuesManipulator should be aware of a server request or the corresponding processed input values.
     * @param $identifier
     * @return bool
     */
    protected function wasImageRemovalRequested($identifier) : bool
    {
        if (!$this->getImageRemovalCommand()) {
            return false;
        }
        
        if (!isset($_POST['cmd']) || !is_array($_POST['cmd'])) {
            return false;
        }
        
        $cmdArr = $_POST['cmd'];
        
        if (!isset($cmdArr[$this->getImageRemovalCommand()])) {
            return false;
        }
        
        $fieldArr = $cmdArr[$this->getImageRemovalCommand()];
            
        if (!isset($fieldArr[$this->getPostVar()])) {
            return false;
        }
        
        $identifierArr = $fieldArr[$this->getPostVar()];

        $requested_identfier = key($identifierArr);

        // The code actually relied on a manipulation of $_POST by ilIdentifiedMultiValuesJsPositionIndexRemover
        return (string) str_replace(
            ilIdentifiedMultiValuesJsPositionIndexRemover::IDENTIFIER_INDICATOR_PREFIX,
            '',
            (string) $requested_identfier
        ) === (string) $identifier;
    }
}
