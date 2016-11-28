<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Services/Form
 */
abstract class ilIdentifiedMultiValuesInputGUI extends ilTextInputGUI implements ilMultiValuesItem
{
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		//$this->setMulti(true);
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
	
	protected function prepareMultiValuesSubmit($values)
	{
		return $this->ensureNonPositionIndexedMultiValues($values);
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
}