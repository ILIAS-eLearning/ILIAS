<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingTextsValuesObjectsConverter implements ilFormValuesManipulator
{
	public function manipulateFormInputValues($objects)
	{
		$values = array();
		
		foreach($objects as $orderingElement)
		{
			/* @var ilAssOrderingElement $orderingElement */
			
			$values[ $orderingElement->getRandomIdentifier() ] = $orderingElement->getContent();
		}
		
		return $values;
	}
	
	public function manipulateFormSubmitValues($values)
	{
		$objects = array();
		$position = 0;
		
		foreach($values as $identifier => $value)
		{
			$element = new ilAssOrderingElement();
			
			$element->setId(null);
			$element->setSolutionIdentifier(null);
			$element->setRandomIdentifier($identifier);
			
			$element->setPosition($position++);
			//$element->setIndentation($depth /* ??? */ );
			
			$element->setContent($value);
			
			$objects[] = $element;
		}
		
		return $objects;
	}
}