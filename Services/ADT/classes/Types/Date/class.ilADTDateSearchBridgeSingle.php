<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTDateSearchBridgeSingle extends ilADTSearchBridgeSingle
{
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTDateDefinition);
	}
	
	
	// table2gui / filter	
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{
			// $this->getADT()->setDate(new ilDate($value, IL_CAL_DATE));
		}
	}
	
	
	// form
	
	public function addToForm()
	{					
		global $lng;
		
		$adt_date = $this->getADT()->getDate();
		$checked = !(!$adt_date || $adt_date->isNull());
		
		$date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());
		$date->enableDateActivation($lng->txt("enabled"), $this->addToElementId("tgl"), $checked);
		$date->setShowTime(false);
		
		$date->setDate($adt_date);
		
		$this->addToParentElement($date);
	}
	
	protected function shouldBeImportedFromPost(array $a_post)
	{
		return (bool)$a_post["tgl"];
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
	
		if($post && $this->shouldBeImportedFromPost($post))
		{
			$date = mktime(12, 0, 0,
				$post["date"]["m"], 
				$post["date"]["d"], 
				$post["date"]["y"]);
			
			// :TODO: all dates are imported as valid 
			
			$date = new ilDate($date, IL_CAL_UNIX);
			
			if($this->getForm() instanceof ilPropertyFormGUI)
			{
				$item = $this->getForm()->getItemByPostVar($this->getElementId());				
				$item->setDate($date);
			}
			else if(array_key_exists($this->getElementId(), $this->table_filter_fields))
			{
				$this->table_filter_fields[$this->getElementId()]->setDate($date);				
				$this->writeFilter($date->get(IL_CAL_DATE));
			}
			
			$this->getADT()->setDate($date);						
		}
		else
		{			
			$this->writeFilter();
			$this->getADT()->setDate();
		}		
	}
			
	
	// db
	
	public function getSQLCondition($a_element_id)
	{
		global $ilDB;
		
		if(!$this->isNull() && $this->isValid())		
		{
			return $a_element_id." = ".$ilDB->quote($this->getADT()->getDate()->get(IL_CAL_DATE), "date");						
		}
	}
	
	public function isInCondition(ilADTDate $a_adt)
	{
		return $this->getADT()->equals($a_adt);
	}		
}

?>