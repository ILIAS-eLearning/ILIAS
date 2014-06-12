<?php

require_once "Services/ADT/classes/Bridges/class.ilADTMultiDBBridge.php";

class ilADTMultiEnumDBBridge extends ilADTMultiDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTMultiEnum);
	}
	
	protected function readMultiRecord($a_set)
	{
		global $ilDB;
		
		$elements = array();
		
		while($row = $ilDB->fetchAssoc($a_set))
		{
			$elements[] = $row[$this->getElementId()];
		}		
		
		$this->getADT()->setSelections($elements);
	}
	
	protected function prepareMultiInsert()
	{
		$res = array();
		
		$type = ($this->getADT() instanceof ilADTMultiEnumNumeric)
			? "integer"
			: "text";
		
		foreach((array)$this->getADT()->getSelections() as $element)
		{					
			$res[] = array($this->getElementId() => array($type, $element));					
		}
		
		return $res;
	}	
}

?>