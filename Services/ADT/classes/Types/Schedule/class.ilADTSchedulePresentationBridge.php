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
		$res = '<div class="schedule_presentation">';
		
		foreach ($schedules as $key => $value) {
			$res .= $lng->txt("time_d")." ".($key + 1).": ".$value."<br />";
		}
		
		$res .= '</div>';
		return $res;
	}
	
	public function getSortable()
	{
		return "";
	}
}

?>