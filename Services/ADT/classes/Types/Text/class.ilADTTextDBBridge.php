<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTTextDBBridge extends ilADTDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTText);
	}
	
	// CRUD
	
	public function readRecord(array $a_row)
	{
		$this->getADT()->setText($a_row[$this->getElementId()]);
	}	
	
	public function prepareInsert(array &$a_fields)
	{
		$a_fields[$this->getElementId()] = array("text", $this->getADT()->getText());
	}	
}

?>