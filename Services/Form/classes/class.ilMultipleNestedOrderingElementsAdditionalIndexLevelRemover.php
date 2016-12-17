<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version       $Id$
 *
 * @package       Services/Form
 */
class ilMultipleNestedOrderingElementsAdditionalIndexLevelRemover implements ilFormValuesManipulator
{
	public function manipulateFormInputValues($inputValues)
	{
		return $inputValues;
	}
	
	public function manipulateFormSubmitValues($submitValues)
	{
		return $this->fetchIndentationsFromSubmitValues($submitValues);
	}
	
	protected function hasContentSubLevel($values)
	{
		if( !is_array($values) || !isset($values['content']) )
		{
			return false;
		}
		
		return true;
	}
	
	protected function hasIndentationsSubLevel($values)
	{
		if( !is_array($values) || !isset($values['indentation']) )
		{
			return false;
		}
		
		return true;
	}
	
	protected function fetchIndentationsFromSubmitValues($values)
	{
		if( $this->hasContentSubLevel($values) && $this->hasIndentationsSubLevel($values) )
		{
			$actualValues = array();
			
			foreach($values['content'] as $index => $value)
			{
				if( !isset($values['indentation'][$index]) )
				{
					$actualValues[$index] = null;
					continue;
				}
				
				$actualValues[$index] = $values['indentation'][$index];
			}
		}
		else
		{
			$actualValues = $values;
		}
		
		return $actualValues;
	}
}