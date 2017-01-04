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
		if( !is_array($values) )
		{
			return $values;
		}
		
		if( !isset($values[ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME]) )
		{
			return $values;
		}
		
		if( !is_array($values[ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME]) )
		{
			return $values;
		}
		
		$actualValues = array();
			
		foreach($values[ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME] as $index => $value)
		{
			if( !isset($values[ilMultipleImagesInputGUI::STORED_IMAGE_SUBFIELD_NAME]) )
			{
				$actualValues[$index] = '';
				continue;
			}
			
			if( !isset($values[ilMultipleImagesInputGUI::STORED_IMAGE_SUBFIELD_NAME][$index]) )
			{
				$actualValues[$index] = '';
				continue;
			}
			
			$actualValues[$index] = $values[ilMultipleImagesInputGUI::STORED_IMAGE_SUBFIELD_NAME][$index];
		}
		
		return $actualValues;
	}
}