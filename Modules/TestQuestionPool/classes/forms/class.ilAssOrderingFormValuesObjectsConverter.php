<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

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
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * @param $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
    
    /**
     * @return string
     */
    public function getPostVar()
    {
        return $this->postVar;
    }
    
    /**
     * @param $postVar
     */
    public function setPostVar($postVar)
    {
        $this->postVar = $postVar;
    }
    
    /**
     * @return null
     */
    public function getImageRemovalCommand()
    {
        return $this->imageRemovalCommand;
    }
    
    /**
     * @param null $imageRemovalCommand
     */
    public function setImageRemovalCommand($imageRemovalCommand)
    {
        $this->imageRemovalCommand = $imageRemovalCommand;
    }
    
    /**
     * @return string
     */
    public function getImageUrlPath()
    {
        return $this->imageUrlPath;
    }
    
    /**
     * @param string $imageUrlPath
     */
    public function setImageUrlPath($imageUrlPath)
    {
        $this->imageUrlPath = $imageUrlPath;
    }
    
    /**
     * @return string
     */
    public function getImageFsPath()
    {
        return $this->imageFsPath;
    }
    
    /**
     * @param string $imageFsPath
     */
    public function setImageFsPath($imageFsPath)
    {
        $this->imageFsPath = $imageFsPath;
    }
    
    /**
     * @return string
     */
    public function getThumbnailPrefix()
    {
        return $this->thumbnailPrefix;
    }
    
    /**
     * @param string $thumbnailPrefix
     */
    public function setThumbnailPrefix($thumbnailPrefix)
    {
        $this->thumbnailPrefix = $thumbnailPrefix;
    }
    
    public function getIndentationsPostVar()
    {
        $postVar = $this->getPostVar();
        $postVar .= self::INDENTATIONS_POSTVAR_SUFFIX;
        $postVar .= self::INDENTATIONS_POSTVAR_SUFFIX_JS;
        
        return $postVar;
    }
    
    protected function needsConvertToValues($elementsOrValues)
    {
        if (!count($elementsOrValues)) {
            return false;
        }
        
        return (current($elementsOrValues) instanceof ilAssOrderingElement);
    }
    
    public function manipulateFormInputValues($elementsOrValues)
    {
        if ($this->needsConvertToValues($elementsOrValues)) {
            $elementsOrValues = $this->collectValuesFromElements($elementsOrValues);
        }
        
        return $elementsOrValues;
    }
    
    protected function collectValuesFromElements(array $elements)
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
    
    protected function getTextContentValueFromObject(ilAssOrderingElement $element)
    {
        return $element->getContent();
    }
    
    protected function getImageContentValueFromObject(ilAssOrderingElement $element)
    {
        $element->setImagePathWeb($this->getImageUrlPath());
        $element->setImagePathFs($this->getImageFsPath());
        $element->setImageThumbnailPrefix($this->getThumbnailPrefix());
        
        return array(
            'title' => $element->getContent(),
            'src' => $element->getPresentationImageUrl()
        );
    }
    
    protected function getStructValueFromObject(ilAssOrderingElement $element)
    {
        return array(
            'answer_id' => $element->getId(),
            'random_id' => $element->getRandomIdentifier(),
            'content' => (string) $element->getContent(),
            'ordering_position' => $element->getPosition(),
            'ordering_indentation' => $element->getIndentation()
        );
    }
    
    protected function needsConvertToElements($valuesOrElements)
    {
        if (!count($valuesOrElements)) {
            return false;
        }
        
        return !(current($valuesOrElements) instanceof ilAssOrderingElement);
    }
    
    public function manipulateFormSubmitValues($valuesOrElements)
    {
        if ($this->needsConvertToElements($valuesOrElements)) {
            $valuesOrElements = $this->constructElementsFromValues($valuesOrElements);
        }
        
        return $valuesOrElements;
    }
    
    public function constructElementsFromValues(array $values)
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
    
    protected function fetchSubmittedUploadFiles()
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
    protected function getFileSubmitDataRestructuredByIdentifiers()
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
    
    protected function wasImageRemovalRequested($identifier)
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
    
        return key($identifierArr) == $identifier;
    }
}
