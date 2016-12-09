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
	
	
	const CONTEXT_MAINTAIN_ELEMENTS = 'maintainElements';
	const CONTEXT_MAINTAIN_HIERARCHY = 'maintainHierarchy';
	
	protected $context = self::CONTEXT_MAINTAIN_ELEMENTS;
	
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
		$values = array();
		
		foreach($objects as $orderingElement)
		{
			/* @var ilAssOrderingElement $orderingElement */
			
			switch( $this->getContext() )
			{
				case self::CONTEXT_MAINTAIN_ELEMENTS:
					
					$values = $this->populateElementValues($orderingElement, $values);
					break;
				
				case self::CONTEXT_MAINTAIN_HIERARCHY:
					
					$values = $this->populateHierarchyValues($orderingElement, $values);
					break;
				
				default:
					throw new ilFormException('unsupported context: '.$this->getContext());
			}
		}
		
		return $values;
	}
	
	protected function populateElementValues(ilAssOrderingElement $element, $values)
	{
		$values[ $element->getRandomIdentifier() ] = $element->getContent();
		
		return $values;
	}
	
	protected function populateHierarchyValues(ilAssOrderingElement $element, $values)
	{
		$values[ $element->getRandomIdentifier()] = array(
			'answer_id' => $element->getId(),
			'random_id' => $element->getRandomIdentifier(),
			'answertext' => (string)$element->getContent(),
			'ordering_position' => $element->getPosition(),
			'ordering_depth' => $element->getIndentation()
		);
		
		return $values;
	}
	
	public function manipulateFormSubmitValues($values)
	{
		if( $this->getContext() == self::CONTEXT_MAINTAIN_HIERARCHY )
		{
			//$values = $this->handleReversedHierarchySubmit($values);
			
			$this->initHierarchySubmitValues();
		}
		
		return $this->convertObjectsFromValues($values);
	}
	
	protected function handleReversedHierarchySubmit($values)
	{
		return array_reverse($values, true);
	}
	
	protected function initHierarchySubmitValues()
	{
		if( isset($_POST[$this->getIndentationsPostVar()]) )
		{
			$stdClassObj = json_decode($_POST[$this->getIndentationsPostVar()], false);
			$this->parseJsHierarchy($stdClassObj, true);
			//$this->getDepthRecursive($stdClassObj, 0, true);
		}
	}
		
	public function convertObjectsFromValues($values)
	{
		if( !$this->objectConversionRequired($values) )
		{
			return $values;
		}
		
		$objects = array();
		$position = 0;
		
		foreach($values as $identifier => $value)
		{
			$element = new ilAssOrderingElement();
			$element->setRandomIdentifier($identifier);
			$element->setContent($value);
			
			$element->setPosition($position++);

			if( $this->getContext() == self::CONTEXT_MAINTAIN_HIERARCHY )
			{
				$element->setIndentation(
					$this->getIndentationByRandomIdentifier($element->getRandomIdentifier())
				);
			}
			
			$objects[] = $element;
		}
		
		return $objects;
	}
	
	protected function objectConversionRequired($values)
	{
		if( !count($values) )
		{
			return false;
		}
		
		if( is_object(current($values)) )
		{
			return false;
		}
		
		return true;
	}
	
	private function getIndentationByRandomIdentifier($randomIdentifier)
	{
		if( !isset($this->randomIdentifierToIndentationMap[$randomIdentifier]) )
		{
			return 0;
		}
		
		return $this->randomIdentifierToIndentationMap[$randomIdentifier];
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
}