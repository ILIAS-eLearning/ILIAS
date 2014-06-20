<?php

require_once "Services/ADT/classes/Bridges/class.ilADTMultiDBBridge.php";

class ilADTScheduleDBBridge extends ilADTMultiDBBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTSchedule);
	}
	
	public function readRecord(array $a_row)
	{
		$this->getADT()->setSchedules(unserialize($a_row[$this->getElementId()]));
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
		
		$type = "text";
		
		foreach((array)$this->getADT()->getSchedules() as $element)
		{					
			$res[] = array($this->getElementId() => array($type, $element));					
		}
		
		return $res;
	}	
}

?>