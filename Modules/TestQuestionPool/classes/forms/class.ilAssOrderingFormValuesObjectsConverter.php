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
	
	const PRESENTED_IMAGE_POSTVAR_SUBINDEX = 'imagename';
	
	const CONTEXT_MAINTAIN_ELEMENT_TEXT = 'maintainItemText';
	const CONTEXT_MAINTAIN_ELEMENT_IMAGE = 'maintainItemImage';
	const CONTEXT_MAINTAIN_HIERARCHY = 'maintainHierarchy';
	
	protected $context = null;
	
	protected $postVar = null;
	
	protected $randomIdentifierToIndentationMap = array();
	
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
	
	public function getIndentationsPostVar()
	{
		$postVar = $this->getPostVar();
		$postVar .= self::INDENTATIONS_POSTVAR_SUFFIX;
		$postVar .= self::INDENTATIONS_POSTVAR_SUFFIX_JS;
		
		return $postVar;
	}
	
	public function manipulateFormInputValues($objects)
	{
		return $this->collectValuesFromObjects($objects);
	}
	
	protected function collectValuesFromObjects($values)
	{
		foreach($values as $identifier => $orderingElement)
		{
			/* @var ilAssOrderingElement $orderingElement */
			
			switch( $this->getContext() )
			{
				case self::CONTEXT_MAINTAIN_ELEMENT_TEXT:
				case self::CONTEXT_MAINTAIN_ELEMENT_IMAGE:
					
					$values[$identifier] = $this->getContentValueFromObject($orderingElement);
					break;
				
				case self::CONTEXT_MAINTAIN_HIERARCHY:
					
					$values[$identifier] = $this->getStructValueFromObject($orderingElement);
					break;
				
				default:
					throw new ilFormException('unsupported context: '.$this->getContext());
			}
		}
		
		return $values;
	}
	
	protected function getContentValueFromObject(ilAssOrderingElement $element)
	{
		return $element->getContent();
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
	
	public function manipulateFormSubmitValues($values)
	{
		if( $this->getContext() == self::CONTEXT_MAINTAIN_HIERARCHY )
		{
			$this->initHierarchySubmitValues();
		}
		
		return $this->constructObjectsFromValues($values);
	}
		
	public function constructObjectsFromValues($values)
	{
		$position = 0;
		
		foreach($values as $identifier => $value)
		{
			if( $value instanceof ilAssOrderingElement )
			{
				continue;
			}
			
			$element = new ilAssOrderingElement();
			$element->setRandomIdentifier($identifier);
			
			$element->setPosition($position++);
			
			if( $this->getContext() == self::CONTEXT_MAINTAIN_HIERARCHY )
			{
				$element->setIndentation(
					$this->getIndentationByRandomIdentifier($element->getRandomIdentifier())
				);
			}
			
			if( $this->getContext() == self::CONTEXT_MAINTAIN_ELEMENT_IMAGE )
			{
				$element->setContent($this->fetchPresentedImageFilename($identifier));
				$element->setUploadImageName($this->fetchSubmittedImageFilename($identifier));
				$element->setUploadImageFile($this->fetchSubnmittedUploadFilename($identifier));
			}
			else
			{
				$element->setContent($value);
			}
			
			$values[$identifier] = $element;
		}
		
		return $values;
	}
	
	protected function fetchPresentedImageFilename($identifier)
	{
		$presentedImageField = $this->fetchPresentedImageField();
		
		if( !isset($presentedImageField[$identifier]) )
		{
			return null;
		}
		
		return $presentedImageField[$identifier];
	}
	
	protected function fetchPresentedImageField()
	{
		if( !isset($_POST[$this->getPostVar()]) )
		{
			return array();
		}
		
		if( !isset($_POST[$this->getPostVar()][self::PRESENTED_IMAGE_POSTVAR_SUBINDEX]) )
		{
			return array();
		}
		
		return $_POST[$this->getPostVar()][self::PRESENTED_IMAGE_POSTVAR_SUBINDEX];
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
	
	private function getIndentationByRandomIdentifier($randomIdentifier)
	{
		if( !isset($this->randomIdentifierToIndentationMap[$randomIdentifier]) )
		{
			return 0;
		}
		
		return $this->randomIdentifierToIndentationMap[$randomIdentifier];
	}
	
	protected function initHierarchySubmitValues()
	{
		if( isset($_POST[$this->getIndentationsPostVar()]) )
		{
			$stdClassObj = json_decode($_POST[$this->getIndentationsPostVar()], false);
			$this->parseJsHierarchy($stdClassObj, true);
		}
	}
	
	private function parseJsHierarchy($new_hierarchy, $with_random_id = false)
	{
		if($with_random_id == true)
		{
			//for test output
			if(is_array($new_hierarchy))
			{
				foreach($new_hierarchy as $id)
				{
					$ordering_depth                  = 0;
					$this->randomIdentifierToIndentationMap[$id->id] = $ordering_depth;
					
					if(is_array($id->children))
					{
						foreach($id->children as $child)
						{
							$this->getDepthRecursive($child, $ordering_depth, true);
						}
					}
				}
			}
		}
		else
		{
			if(is_array($new_hierarchy))
			{
				foreach($new_hierarchy as $id)
				{
					$ordering_depth           = 0;
					$this->randomIdentifierToIndentationMap[] = $ordering_depth;
					
					if(is_array($id->children))
					{
						foreach($id->children as $child)
						{
							$this->getDepthRecursive($child, $ordering_depth, $with_random_id);
						}
					}
				}
			}
		}
	}
	
	private function getDepthRecursive($child, $ordering_depth, $with_random_id = false)
	{
		// for test ouput
		if(is_array($child->children))
		{
			foreach($child->children as $grand_child)
			{
				$ordering_depth++;
				$this->randomIdentifierToIndentationMap[$child->id] = $ordering_depth;
				$this->getDepthRecursive($grand_child, $ordering_depth, true);
			}
		}
		else
		{
			$ordering_depth++;
			$this->randomIdentifierToIndentationMap[$child->id] = $ordering_depth;
		}
	}
	
	protected function handleReversedHierarchySubmit($values)
	{
		return array_reverse($values, true);
	}
}