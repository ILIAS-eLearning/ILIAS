<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTSchedulePresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTSchedule);
	}
	
	public function getHTML()
	{
		global $lng;
		
		$schedules = $this->getADT()->getSchedules();
		$res = "";
		
		foreach ($schedules as $key => $value) {
			$res .= $lng->txt("time_d")." ".$key.": ".$value;
		}
		
		return $res;
	}
	
	public function getSortable()
	{
		return "";
	}
}

?>