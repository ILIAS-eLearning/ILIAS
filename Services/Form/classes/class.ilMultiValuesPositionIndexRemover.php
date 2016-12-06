<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilMultiValuesPositionIndexRemover implements ilFormValuesManipulator
{
	public function manipulateFormInputValues($inputValues)
	{
		return $inputValues;
	}
	
	public function manipulateFormSubmitValues($submitValues)
	{
		return $this->ensureNonPositionIndexedMultiValues($submitValues);
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