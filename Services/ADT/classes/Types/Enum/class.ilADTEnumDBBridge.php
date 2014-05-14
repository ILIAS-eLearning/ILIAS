<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTEnumDBBridge extends ilADTDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTEnum);
	}
	
	// CRUD
	
	public function readRecord(array $a_row)
	{
		$this->getADT()->setSelection($a_row[$this->getElementId()]);
	}	
	
	public function prepareInsert(array &$a_fields)
	{
		$type = ($this->getADT() instanceof ilADTEnumNumeric)
			? "integer"
			: "text";
		$a_fields[$this->getElementId()] = array($type, $this->getADT()->getSelection());
	}	
}

?>