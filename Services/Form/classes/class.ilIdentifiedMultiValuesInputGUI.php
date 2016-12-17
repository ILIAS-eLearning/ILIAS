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
	protected $formValuesManipulationChain = array();
	
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		
		require_once 'Services/Form/classes/class.ilFormSubmitRecursiveSlashesStripper.php';
		$this->addFormValuesManipulator(new ilFormSubmitRecursiveSlashesStripper());
		
		require_once 'Services/Form/classes/class.ilIdentifiedMultiValuesJsPositionIndexRemover.php';
		$this->addFormValuesManipulator(new ilIdentifiedMultiValuesJsPositionIndexRemover());
		
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
	
	protected function buildMultiValueFieldId($identifier, $positionIndex = null, $subFieldIndex = '')
	{
		$basicPostVar = $this->getPostVar();
		$this->setPostVar($this->buildMultiValuePostVar($identifier, $positionIndex, $subFieldIndex));
		
		// uses getPostVar() internally, our postvar does not have the counter included
		$multiValueFieldId = $this->getFieldId();
		// now ALL brackets ("[", "]") are escaped, even the ones for the counter
		
		$this->setPostVar($basicPostVar);
		return $multiValueFieldId;
	}
	
	protected function buildMultiValuePostVar($identifier, $positionIndex = null, $subFieldIndex = null)
	{
		$elemPostVar = $this->getPostVar();
		
		if( $subFieldIndex !== null )
		{
			$elemPostVar .= "[$subFieldIndex]";
		}
		
		$elemPostVar .= "[$identifier]";
		
		if( $positionIndex !== null )
		{
			$elemPostVar .= "[$positionIndex]";
		}
		
		return $elemPostVar;
	}
	
	protected function buildMultiValueSubmitVar($identifier, $positionIndex, $submitCommand)
	{
		$elemSubmitVar = "cmd[{$submitCommand}{$this->getFieldId()}]";
		$elemSubmitVar .= "[$identifier][$positionIndex]";
		
		return $elemSubmitVar;
	}
	
	final public function setValueByArray($a_values)
	{
		if( !is_array($a_values[$this->getPostVar()]) )
		{
			$a_values[$this->getPostVar()] = array();
		}
		
		$a_values[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
			$a_values[$this->getPostVar()]
		);
		
		parent::setValueByArray($a_values);
	}
	
	final public function checkInput()
	{
		if( !is_array($_POST[$this->getPostVar()]) )
		{
			$_POST[$this->getPostVar()] = array();
		}
		
		$_POST[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
			$_POST[$this->getPostVar()]
		);
		
		return $this->onCheckInput();
	}
	
	abstract public function onCheckInput();
	
	final protected function prepareMultiValuesInput($values)
	{
		foreach($this->getFormValuesManipulators() as $manipulator)
		{
			/* @var ilFormValuesManipulator $manipulator */
			$values = $manipulator->manipulateFormInputValues($values);
		}
		
		return $values;
	}
	
	final protected function prepareMultiValuesSubmit($values)
	{
		foreach($this->getFormValuesManipulators() as $manipulator)
		{
			/* @var ilFormValuesManipulator $manipulator */
			$values = $manipulator->manipulateFormSubmitValues($values);
		}
		
		return $values;
	}
	
	protected function getFormValuesManipulators()
	{
		return $this->formValuesManipulationChain;
	}
	
	protected function addFormValuesManipulator(ilFormValuesManipulator $manipulator)
	{
		$this->formValuesManipulationChain[] = $manipulator;
	}
}