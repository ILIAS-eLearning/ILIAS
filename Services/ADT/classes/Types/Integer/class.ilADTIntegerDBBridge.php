<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTIntegerDBBridge extends ilADTDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTInteger);
	}
	
	
	// CRUD
	
	public function readRecord(array $a_row)
	{
		$this->getADT()->setNumber($a_row[$this->getElementId()]);
	}	
	
	public function prepareInsert(array &$a_fields)
	{
		$a_fields[$this->getElementId()] = array("integer", $this->getADT()->getNumber());
	}	
}

?>