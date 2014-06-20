<?php

class ilADTScheduleDefinition extends ilADTDefinition
{
	// default
	
	public function reset() 
	{
		parent::reset();
	}
	
	// comparison
		
	public function isComparableTo(ilADT $a_adt)
	{
		return false;
	}	
	
	
	// ADT instance
	
	public function getADTInstance()
	{
		include_once "Services/ADT/classes/Types/Schedule/class.ilADTSchedule.php";
		return new ilADTSchedule($this);
	}
}

?>