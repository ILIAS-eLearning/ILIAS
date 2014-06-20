<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTScheduleFormBridge extends ilADTFormBridge
{
	protected $option_infos; // [array]
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTSchedule);
	}
	
	public function setOptionInfos(array $a_info = null)
	{
		$this->option_infos = $a_info;
	}
	
	public function addToForm()
	{
		require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
		global $lng;
				
		$def = $this->getADT()->getCopyOfDefinition();
		
		$schedule = $this->getADT()->getSchedules();
		
		$sbox = new ilDateDurationInputGUI($this->getTitle(), $this->getElementId()."[]");
		$sbox->setShowDate(false);
		$sbox->setShowTime(true);
		$sbox->setMulti(true);
		$sbox->setStartText($lng->txt("time_d")." ".$sbox->getMultiCounterElement());
		$sbox->setEndText($lng->txt("until"));
		
		if (!$schedule) {
			$sbox->setStart(new ilDateTime(mktime(9), IL_CAL_UNIX));
			$sbox->setEnd(new ilDateTime(mktime(17), IL_CAL_UNIX));
		}
		else {
			$this->setValues($sbox, $schedule);
		}

		$this->addBasicFieldProperties($sbox, $def);

		$this->addToParentElement($sbox);
	}
	
	public function importFromPost()
	{
		$vals = $this->rectifyPostInput($this->getForm()->getInput($this->getElementId()));
		
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setSchedules($vals);
	
		$field = $this->getForm()->getItemByPostvar($this->getElementId()."[]");
		$field->setMultiValues($vals);
	}	
	
	protected function isActiveForSubItems($a_parent_option = null)
	{		
		// TODO: What is this?
	}
	
	protected function rectifyPostInput($a_vals) {
		$ret = array();
		
		while (count($a_vals) >= 4) {
			$_1 = array_shift($a_vals);
			$_2 = array_shift($a_vals);
			$_3 = array_shift($a_vals);
			$_4 = array_shift($a_vals);
			
			$ret[] = str_pad($_1["start"]["time"]["h"], 2, "0", STR_PAD_LEFT).":".str_pad($_2["start"]["time"]["m"], 2, "0", STR_PAD_LEFT)."-".
					 str_pad($_3["end"]["time"]["h"], 2, "0", STR_PAD_LEFT).":".str_pad($_4["end"]["time"]["m"], 2, "0", STR_PAD_LEFT);
		}
		
		return $ret;
	}
	
	protected function setValues($a_sbox, $a_schedule) {
		$first = array_shift($a_schedule);
		$first = explode("-", $first);
		$first[0] = explode(":", $first[0]);
		$first[1] = explode(":", $first[1]);
		$a_sbox->setStart(new ilDateTime(mktime(intval($first[0][0]), intval($first[0][1])), IL_CAL_UNIX));
		$a_sbox->setEnd(new ilDateTime(mktime(intval($first[1][0]), intval($first[1][1])), IL_CAL_UNIX));
	
		$a_sbox->setMoreValues($a_schedule);
	}
}

?>