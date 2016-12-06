<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingTextsValuesObjectDeriver implements ilFormValuesManipulator
{
	public function manipulateFormInputValues($objects)
	{
		$values = $objects;
		
		return $values;
	}
	
	public function manipulateFormSubmitValues($values)
	{
		$objects = array();
		$position = 0;
		
		foreach($values as $identifier => $value)
		{
			$element = new ilAssOrderingElement();
			
			$element->setRandomIdentifier($identifier);
			//$element->setSolutionIdentifier(null);
			
			$element->setPosition($position++);
			//$element->setIndentation($position++);
			
			$element->setContent($value);
			
			$objects[] = $element;
		}
		
		return $objects;
	}
}