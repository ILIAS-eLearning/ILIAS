<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';
require_once 'Services/Form/interfaces/interface.ilFormSubmitManipulator.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Services/Form
 */
abstract class ilIdentifiedMultiValuesInputGUI extends ilTextInputGUI implements ilMultiValuesItem, ilFormSubmitManipulator
{
	
	protected $formSubmitManipulationChain = array();
	
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		
		$this->addFormSubmitManipulator($this);
		
		//$this->setMulti(true); // this is another planet, do not enable (!)
	}
	
	public function setValue($value)
	{
		$this->setMultiValues($value);
	}
	
	public function getValue()
	{
		return $this->getMultiValues();
	}
	
	public function setValues($value)
	{
		$this->setMultiValues($value);
	}
	
	public function getValues()
	{
		return $this->getMultiValues();
	}
	
	public function setMultiValues(array $a_values)
	{
		$this->multi_values = $a_values;
	}
	
	protected function buildMultiValueFieldId($positionIndex, $subFieldIndex = '')
	{
		$basicPostVar = $this->getPostVar();
		$this->setPostVar($this->buildMultiValuePostVar($positionIndex, $subFieldIndex));
		
		// uses getPostVar() internally, our postvar does not have the counter included
		$multiValueFieldId = $this->getFieldId();
		// now ALL brackets ("[", "]") are escaped, even the ones for the counter
		
		$this->setPostVar($basicPostVar);
		return $multiValueFieldId;
	}
	
	protected function buildMultiValuePostVar($positionIndex, $subFieldIndex = '')
	{
		$elemPostVar = $this->getPostVar();
		
		if( strlen($subFieldIndex) )
		{
			$elemPostVar .= "[$subFieldIndex]";
		}
		
		$elemPostVar .= "[{$this->getMultiValueKeyByPosition($positionIndex)}][$positionIndex]";
		
		return $elemPostVar;
	}
	
	protected function buildMultiValueSubmitVar($positionIndex, $submitCommand)
	{
		$elemSubmitVar = "cmd[{$submitCommand}{$this->getFieldId()}]";
		$elemSubmitVar .= "[{$this->getMultiValueKeyByPosition($positionIndex)}][{$positionIndex}]";
		
		return $elemSubmitVar;
	}
	
	abstract protected function getMultiValueKeyByPosition($positionIndex);
	
	/**
	 * TODO: implement as chain item, when post data manipulation chain is available
	 * 
	 * @return array
	 */
	protected function deriveObjectsFromPostData($postDataValues)
	{
		$keyIdentifiedValues = array();
		
		foreach($positionIndexedValues as $valueKey => $value)
		{
			if( $this->isPositionIndexedValue($value) )
			{
				$value = $this->removeMultiValuePositionIndex($value);
			}
			
			$keyIdentifiedValues[$valueKey] = $value;
		}
		
		return $keyIdentifiedValues;
	}
	
	final public function setValueByArray($a_values)
	{
		$a_values[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
			$a_values[$this->getPostVar()]
		);
		
		parent::setValueByArray($a_values);
	}
	
	final public function checkInput()
	{
		$_POST[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
			$_POST[$this->getPostVar()]
		);
		
		return $this->onCheckInput();
	}
	
	abstract public function onCheckInput();
	
	protected function prepareMultiValuesSubmit($values)
	{
		foreach($this->getFormSubmitManipulators() as $manipulator)
		{
			/* @var ilFormSubmitManipulator $manipulator */
			$values = $manipulator->manipulateFormSubmitValues($values);
		}
		
		return $values;
	}
	
	protected function getFormSubmitManipulators()
	{
		$this->formSubmitManipulationChain;
	}
	
	protected function addFormSubmitManipulator(ilFormSubmitManipulator $manipulator)
	{
		$this->formSubmitManipulationChain[] = $manipulator;
	}
	
	public function manipulateFormSubmitValues($values)
	{
		return $this->ensureNonPositionIndexedMultiValues($values);
	}
	
	protected function ensureNonPositionIndexedMultiValues($positionIndexedValues)
	{
		$keyIdentifiedValues = array();
		
		foreach($positionIndexedValues as $valueKey => $value)
		{
			if( $this->isPositionIndexedValue($value) )
			{
				$value = $this->removeMultiValuePositionIndex($value);
			}
			
			$keyIdentifiedValues[$valueKey] = $value;
		}
		
		return $keyIdentifiedValues;
	}
	
	protected function isPositionIndexedValue($value)
	{
		switch(true)
		{
			case !is_array($value):
			case count($value) != 1:
			case !is_integer(key($value)):
			case !is_scalar(current($value)) && !is_object(current($value)):
				
				return false;
		}
		
		return true;
	}
	
	protected function removeMultiValuePositionIndex($value)
	{
		return current($value);
	}
}