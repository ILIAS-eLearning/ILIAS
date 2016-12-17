<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilIdentifiedMultiValuesJsPositionIndexRemover implements ilFormValuesManipulator
{
	const IDENTIFIER_INDICATOR_PREFIX = 'IDENTIFIER~';
	
	public function manipulateFormInputValues($inputValues)
	{
		return $this->brandIdentifiersWithIndicator($inputValues);
	}
	
	protected function brandIdentifiersWithIndicator($values)
	{
		foreach($values as $identifier => $val)
		{
			$values[$this->getIndicatorBrandedIdentifier($identifier)] = $val;
			unset($values[$identifier]);
		}
		
		return $values;
	}
	
	protected function getIndicatorBrandedIdentifier($identifier)
	{
		return self::IDENTIFIER_INDICATOR_PREFIX . $identifier;
	}
	
	public function manipulateFormSubmitValues($submitValues)
	{
		return $this->removePositionIndexLevels($submitValues);
	}
	
	protected function removePositionIndexLevels($values)
	{
		foreach($values as $key => $val)
		{
			unset($values[$key]);
			
			if( $this->isValueIdentifier($key) )
			{
				$key = $this->removeIdentifierIndicator($key);
				$val = $this->fetchPositionIndexedValue($val);
			}
			elseif( is_array($val) )
			{
				$val = $this->removePositionIndexLevels($val);
			}
			
			$values[$key] = $val;
		}
		
		return $values;
	}
	
	protected function isValueIdentifier($key)
	{
		$indicatorPrefixLength = self::IDENTIFIER_INDICATOR_PREFIX;
		
		if( strlen($key) <= strlen($indicatorPrefixLength) )
		{
			return false;
		}
		
		if( substr($key, 0, strlen($indicatorPrefixLength)) != $indicatorPrefixLength )
		{
			return false;
		}
		
		return true;
	}
	
	protected function removeIdentifierIndicator($key)
	{
		return str_replace(self::IDENTIFIER_INDICATOR_PREFIX, '', $key);
	}
	
	protected function fetchPositionIndexedValue($value)
	{
		return current($value);
	}
}