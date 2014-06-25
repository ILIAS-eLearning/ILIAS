<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTDateTimeSearchBridgeSingle extends ilADTSearchBridgeSingle
{
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTDateTimeDefinition);
	}
	
	
	// table2gui / filter
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{
			$this->getADT()->setDate(new ilDateTime($value, IL_CAL_DATETIME));
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
		$date->setShowTime(true);
		
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
			$date = mktime(
				$post["time"]["h"], 
				$post["time"]["m"], 
				1, 
				$post["date"]["m"], 
				$post["date"]["d"], 
				$post["date"]["y"]);
			
			// :TODO: all dates are imported as valid 
			
			$date = new ilDateTime($date, IL_CAL_UNIX);
			
			if($this->getForm() instanceof ilPropertyFormGUI)
			{
				$item = $this->getForm()->getItemByPostVar($this->getElementId());		
				$item->setDate($date);	
			}
			else if(array_key_exists($this->getElementId(), $this->table_filter_fields))
			{			
				$this->table_filter_fields[$this->getElementId()]->setDate($date);				
				$this->writeFilter($date->get(IL_CAL_DATETIME));
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
			return $a_element_id." = ".$ilDB->quote($this->getADT()->getDate()->get(IL_CAL_DATETIME), "timestamp");				
		}
	}
	
	public function isInCondition(ilADTDateTime $a_adt)
	{
		return $this->getADT()->equals($a_adt);
	}		
	
	
	//  import/export	
		
	public function getSerializedValue()
	{		
		if(!$this->isNull() && $this->isValid())		
		{			
			return serialize(array($this->getADT()->getDate()->get(IL_CAL_DATETIME)));
		}		
	}
	
	public function setSerializedValue($a_value)
	{		
		$a_value = unserialize($a_value);
		if(is_array($a_value))
		{			
			$this->getADT()->setDate(new ilDateTimet($a_value[0], IL_CAL_DATETIME));			
		}		
	}
}

?>