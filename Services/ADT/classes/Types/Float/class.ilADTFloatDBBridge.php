<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTFloatDBBridge extends ilADTDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTFloat);
	}
	
	
	// CRUD
	
	public function readRecord(array $a_row)
	{
		$this->getADT()->setNumber($a_row[$this->getElementId()]);
	}	
	
	public function prepareInsert(array &$a_fields)
	{
		$a_fields[$this->getElementId()] = array("float", $this->getADT()->getNumber());
	}	
}

?>