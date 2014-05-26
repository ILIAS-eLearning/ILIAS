<?php

require_once "Services/ADT/classes/Bridges/class.ilADTMultiDBBridge.php";

class ilADTMultiEnumDBBridge extends ilADTMultiDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTMultiEnum);
	}
	
	public function readRecord(array $a_row)
	{
		$this->getADT()->setSelections(unserialize($a_row[$this->getElementId()]));
	}
	
	protected function readMultiRecord($a_set)
	{
		throw new Exception("Why is this called?");
	}
	
	public function prepareInsert(array &$a_fields) {
		$vals = $this->prepareMultiInsert();
		$tmp = array();
		foreach ($vals as $val) {
			$tmp[] = $val[$this->getElementId()][1];
		}

		$a_fields[$this->getElementId()] = array("text", serialize($tmp));
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