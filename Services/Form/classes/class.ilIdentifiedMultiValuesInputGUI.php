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
	protected $formSubmitManipulationChain = array();
	protected $formInputManipulationChain = array();
	
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		
		require_once 'Services/Form/classes/class.ilFormSubmitRecursiveSlashesStripper.php';
		$this->addFormSubmitManipulator(new ilFormSubmitRecursiveSlashesStripper());
		
		require_once 'Services/Form/classes/class.ilMultiValuesPositionIndexRemover.php';
		$this->addFormSubmitManipulator(new ilMultiValuesPositionIndexRemover());
		
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
	
	final public function setMultiValues(array $values)
	{
		$this->multi_values = $this->prepareMultiValuesInput($values);
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
	
	final protected function prepareMultiValuesInput($values)
	{
		foreach($this->getFormInputManipulators() as $manipulator)
		{
			/* @var ilFormValuesManipulator $manipulator */
			$values = $manipulator->manipulateFormInputValues($values);
		}
		
		return $values;
	}
	
	final protected function prepareMultiValuesSubmit($values)
	{
		foreach($this->getFormSubmitManipulators() as $manipulator)
		{
			/* @var ilFormValuesManipulator $manipulator */
			$values = $manipulator->manipulateFormSubmitValues($values);
		}
		
		return $values;
	}
	
	protected function getFormInputManipulators()
	{
		return $this->formInputManipulationChain;
	}
	
	protected function addFormInputManipulator(ilFormValuesManipulator $manipulator)
	{
		$this->formInputManipulationChain[] = $manipulator;
	}
	
	protected function getFormSubmitManipulators()
	{
		return $this->formSubmitManipulationChain;
	}
	
	protected function addFormSubmitManipulator(ilFormValuesManipulator $manipulator)
	{
		$this->formSubmitManipulationChain[] = $manipulator;
	}
}