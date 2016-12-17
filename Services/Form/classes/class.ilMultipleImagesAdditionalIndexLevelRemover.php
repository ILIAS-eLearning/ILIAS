<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilMultipleImagesAdditionalIndexLevelRemover implements ilFormValuesManipulator
{
	public function manipulateFormInputValues($inputValues)
	{
		return $inputValues;
	}
	
	public function manipulateFormSubmitValues($submitValues)
	{
		return $this->fetchFilenamesFromSubmitValues($submitValues);
	}
	
	protected function fetchFilenamesFromSubmitValues($values)
	{
		$actualValues = $values;
		
		if( is_array($values) && isset($values['count']) && is_array($values['count']) )
		{
			$actualValues = array();
			
			foreach($values['count'] as $index => $value)
			{
				if( !isset($values['imagename']) )
				{
					$actualValues[$index] = '';
					continue;
				}
				
				if( !isset($values['imagename'][$index]) )
				{
					$actualValues[$index] = '';
					continue;
				}
				
				$actualValues[$index] = $values['imagename'][$index];
			}
		}
		
		return $actualValues;
	}
}