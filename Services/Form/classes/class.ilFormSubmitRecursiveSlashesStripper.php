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
	/**
	 * @param array $inputValues
	 * @return array
	 */
	public function manipulateFormInputValues($inputValues)
	{
		return $inputValues;
	}
	
	/**
	 * @param array $submitValues
	 * @return array|mixed|string
	 * @throws ilFormException
	 */
	public function manipulateFormSubmitValues($submitValues)
	{
		$this->checkForNonObjectsOnly($submitValues);
		return ilUtil::stripSlashesRecursive($submitValues);
	}
	
	/**
	 * @param $submitValues
	 * @throws ilFormException
	 */
	protected function checkForNonObjectsOnly($submitValues)
	{
		if( !$this->hasNonObjectsOnlyRecursive($submitValues) )
		{
			require_once 'Services/Form/exceptions/class.ilFormException.php';
			throw new ilFormException(__METHOD__.' -> objects not supported');
		}
	}
	
	/**
	 * @param $mixed
	 * @return bool
	 */
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