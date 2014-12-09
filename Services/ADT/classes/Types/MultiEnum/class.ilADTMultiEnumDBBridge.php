<?php

require_once "Services/ADT/classes/Bridges/class.ilADTMultiDBBridge.php";

class ilADTMultiEnumDBBridge extends ilADTMultiDBBridge
{	
	protected $fake_single;
	
	const SEPARATOR = "~|~";
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTMultiEnum);
	}
	
	public function setFakeSingle($a_status)
	{
		$this->fake_single = (bool)$a_status;
	}
	
	protected function doSingleFake()
	{
		return $this->fake_single;
	}
	
	public function readRecord(array $a_row)
	{
		global $ilDB;
		
		if(!$this->doSingleFake())
		{				
			$sql = "SELECT ".$this->getElementId().
				" FROM ".$this->getSubTableName().
				" WHERE ".$this->buildPrimaryWhere(); 
			$set = $ilDB->query($sql);	

			$this->readMultiRecord($set);
		}
		else
		{
			if(trim($a_row[$this->getElementId()]))
			{
				$value = explode(self::SEPARATOR, $a_row[$this->getElementId()]);
				array_pop($value);
				array_shift($value);
				$this->getADT()->setSelections($value);
			}
		}
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
	
	public function prepareInsert(array &$a_fields)
	{
		if($this->doSingleFake())
		{
			$values = (array)$this->getADT()->getSelections();
			if(sizeof($values))
			{
				$values = self::SEPARATOR.implode(self::SEPARATOR, $values).self::SEPARATOR;
			}		
			$a_fields[$this->getElementId()] = array("text", $values);
		}
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