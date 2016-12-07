<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Services/Form
 */
class ilFormSubmitRecursiveSlashesStripper implements ilFormValuesManipulator
{
	public function manipulateFormInputValues($inputValues)
	{
		return $inputValues;
	}
	
	public function manipulateFormSubmitValues($submitValues)
	{
		if( !$this->hasNonObjectsOnlyRecursive($submitValues) )
		{
			require_once 'Services/Form/exceptions/class.ilFormException.php';
			throw new ilFormException(__METHOD__.' -> objects not supported');
		}
		
		return ilUtil::stripSlashesRecursive($submitValues);
	}
	
	protected function hasNonObjectsOnlyRecursive($mixed)
	{
		if( is_object($mixed) )
		{
			return false;
		}
		
		if( is_array($mixed) )
		{
			foreach($mixed as $mixing => $mixer)
			{
				if( !$this->hasNonObjectsOnlyRecursive($mixer) )
				{
					return false;
				}
			}
		}
		
		return true;
	}
}