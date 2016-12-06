<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilMultiValuesPositionIndexRemover.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilMultiFilesPositionIndexRemover extends ilMultiValuesPositionIndexRemover
{
	protected $postVar = null;
	
	public function getPostVar()
	{
		return $this->postVar;
	}
	
	public function setPostVar($postVar)
	{
		$this->postVar = $postVar;
	}

	public function manipulateFormSubmitValues($values)
	{
		if( $this->isFileSubmitAvailable() )
		{
			$this->prepareFileSubmit();
		}
		
		return $this->fetchFilenamesFromSubmitValues($values);
	}
	
	protected function isFileSubmitAvailable()
	{
		if( !isset($_FILES[$this->getPostVar()]) )
		{
			return false;
		}
		
		if( !is_array($_FILES[$this->getPostVar()]) )
		{
			return false;
		}
		
		if( !in_array('tmp_name', array_keys($_FILES[$this->getPostVar()])) )
		{
			return false;
		}
		
		return true;
	}
	
	protected function prepareFileSubmit()
	{
		$_FILES[$this->getPostVar()] = $this->prepareMultiFilesSubmitValues(
			$_FILES[$this->getPostVar()]
		);
	}
	
	/**
	 * @param $filesSubmit
	 * @return mixed
	 */
	protected function prepareMultiFilesSubmitValues($filesSubmitValues)
	{
		foreach($filesSubmitValues as $phpUploadField => $fileUploadInfo)
		{
			$fileUploadInfo['image'] = $this->ensureNonPositionIndexedMultiValues(
				$fileUploadInfo['image']
			);
			
			$filesSubmitValues[$phpUploadField] = $fileUploadInfo;
		}
		
		return $filesSubmitValues;
	}
	
	protected function fetchFilenamesFromSubmitValues($values)
	{
		$actualValues = $values;
		
		if( is_array($values) && isset($values['count']) && is_array($values['count']) )
		{
			$actualValues = array();
			
			foreach($values['count'] as $index => $value)
			{
				$actualValues[$index] = $values['imagename'][$index];
			}
		}
		
		return $actualValues;
	}
}