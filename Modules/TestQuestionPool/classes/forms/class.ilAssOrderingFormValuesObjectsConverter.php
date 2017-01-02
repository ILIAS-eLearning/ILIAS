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
	
	protected $context = null;
	
	protected $postVar = null;
	
	protected $imageRemovalCommand = null;
	
	/**
	 * @var assOrderingQuestion
	 */
	protected $questionOBJ;
	
	/**
	 * ilAssOrderingFormValuesObjectsConverter constructor.
	 */
	public function __construct()
	{
	}
	
	public function getContext()
	{
		return $this->context;
	}
	
	public function setContext($context)
	{
		$this->context = $context;
	}
	
	public function getPostVar()
	{
		return $this->postVar;
	}
	
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
	 * @return assOrderingQuestion
	 */
	public function getQuestionOBJ()
	{
		return $this->questionOBJ;
	}
	
	/**
	 * @param assOrderingQuestion $questionOBJ
	 */
	public function setQuestionOBJ($questionOBJ)
	{
		$this->questionOBJ = $questionOBJ;
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
		if( !count($elementsOrValues) )
		{
			return false;
		}
		
		return ( current($elementsOrValues) instanceof ilAssOrderingElement );
	}
	
	public function manipulateFormInputValues($elementsOrValues)
	{
		if( $this->needsConvertToValues($elementsOrValues) )
		{
			$elementsOrValues = $this->collectValuesFromElements($elementsOrValues);
		}
		
		return $elementsOrValues;
	}
	
	protected function collectValuesFromElements(array $elements)
	{
		$values = array();
		
		foreach($elements as $identifier => $orderingElement)
		{
			switch( $this->getContext() )
			{
				case self::CONTEXT_MAINTAIN_ELEMENT_TEXT:
					
					$values[$identifier] = $this->getTextContentValueFromObject(
						$orderingElement
					);
					break;
				
				case self::CONTEXT_MAINTAIN_ELEMENT_IMAGE:
					
					$values[$identifier] = $this->getTextContentValueFromObject(
						$orderingElement
					);
					break;
				
				case self::CONTEXT_MAINTAIN_HIERARCHY:
					
					$values[$identifier] = $this->getStructValueFromObject(
						$orderingElement
					);
					break;
				
				default:
					throw new ilFormException('unsupported context: '.$this->getContext());
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
		$imagePath = $this->getQuestionOBJ()->getImagePathWeb();
		$thumbPrefix = $this->getQuestionOBJ()->getThumbPrefix();
		
		$originalFile = $imagePath . $element->getContent();
		$thumbFile = $imagePath . $thumbPrefix . $element->getContent();
		
		
		if( $this->getQuestionOBJ()->getThumbSize() && @file_exists($thumbFile) )
		{
			$imageFile = $thumbFile;
		}
		else
		{
			$imageFile = $originalFile;
		}
		
		return array(
			'src' => $imageFile, 'title' => $element->getContent()
		);
	}
	
	protected function getStructValueFromObject(ilAssOrderingElement $element)
	{
		return array(
			'answer_id' => $element->getId(),
			'random_id' => $element->getRandomIdentifier(),
			'content' => (string)$element->getContent(),
			'ordering_position' => $element->getPosition(),
			'ordering_indentation' => $element->getIndentation()
		);
	}
	
	protected function needsConvertToElements($valuesOrElements)
	{
		if( !count($valuesOrElements) )
		{
			return false;
		}
		
		return !( current($valuesOrElements) instanceof ilAssOrderingElement );
	}
	
	public function manipulateFormSubmitValues($valuesOrElements)
	{
		if( $this->needsConvertToElements($valuesOrElements) )
		{
			$valuesOrElements = $this->constructElementsFromValues($valuesOrElements);
		}
		
		return $valuesOrElements;
	}
	
	public function constructElementsFromValues(array $values)
	{
		$elements = array();
		
		$position = 0;
		
		foreach($values as $identifier => $value)
		{
			$element = new ilAssOrderingElement();
			$element->setRandomIdentifier($identifier);
			
			$element->setPosition($position++);
			
			if( $this->getContext() == self::CONTEXT_MAINTAIN_HIERARCHY )
			{
				$element->setIndentation($value);
			}
			else
			{
				$element->setContent($value);
			}
			
			if( $this->getContext() == self::CONTEXT_MAINTAIN_ELEMENT_IMAGE )
			{
				$element->setUploadImageName($this->fetchSubmittedImageFilename($identifier));
				$element->setUploadImageFile($this->fetchSubnmittedUploadFilename($identifier));
				
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
	
	protected function fetchSubnmittedUploadFilename($identifier)
	{
		$fileUpload = $this->fetchElementFileUpload($identifier);
		return $this->fetchSubmittedFileUploadProperty($fileUpload, 'tmp_name');
	}
	
	protected function fetchSubmittedFileUploadProperty($fileUpload, $property)
	{
		if( !isset($fileUpload[$property]) || !strlen($fileUpload[$property]) )
		{
			return null;
		}
		
		return $fileUpload[$property];
	}
	
	protected function fetchElementFileUpload($identifier)
	{
		$uploadFiles = $this->fetchSubmittedUploadFiles();
		
		if( !isset($uploadFiles[$identifier]) )
		{
			return array();
		}
		
		return $uploadFiles[$identifier];
	}
	
	protected function fetchSubmittedUploadFiles()
	{
		$submittedUploadFiles = array();
		
		foreach($this->getFilesSubmit() as $uploadProperty => $postField)
		{
			foreach($postField as $postVariable => $valueElement)
			{
				foreach($valueElement as $elementIdentifier => $uploadValue)
				{
					if( !isset($submittedUploadFiles[$elementIdentifier]) )
					{
						$submittedUploadFiles[$elementIdentifier] = array();
					}
					
					$submittedUploadFiles[$elementIdentifier][$uploadProperty] = $uploadValue;
				}
			}
		}
		
		return $submittedUploadFiles;
	}
	
	protected function getFilesSubmit()
	{
		if( !isset($_FILES[$this->getPostVar()]) )
		{
			return array();
		}
		
		return $_FILES[$this->getPostVar()];
	}
	
	protected function wasImageRemovalRequested($identifier)
	{
		if( !$this->getImageRemovalCommand() )
		{
			return false;
		}
		
		if( !isset($_POST['cmd']) || !is_array($_POST['cmd']) )
		{
			return false;
		}
		
		if( !isset($_POST['cmd'][$this->getImageRemovalCommand()]) )
		{
			return false;
		}
		
		return key($_POST['cmd'][$this->getImageRemovalCommand()]) == $identifier;
	}
}