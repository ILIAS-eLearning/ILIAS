<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTDateTimeDBBridge extends ilADTDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTDateTime);
	}
	
	// CRUD
	
	public function readRecord(array $a_row)
	{
		$date = null;
		if($a_row[$this->getElementId()])
		{
			$date = new ilDateTime($a_row[$this->getElementId()], IL_CAL_DATETIME);
		}
		$this->getADT()->setDate($date);
	}	
	
	public function prepareInsert(array &$a_fields)
	{
		$date = $this->getADT()->getDate();
		if($date instanceof ilDateTime)
		{
			$date = $date->get(IL_CAL_DATETIME);
		}
		$a_fields[$this->getElementId()] = array("timestamp", $date);
	}	
}

?>