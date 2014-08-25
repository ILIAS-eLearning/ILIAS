<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTDateSearchBridgeSingle extends ilADTSearchBridgeSingle
{
	protected $text_input; // [bool]
	
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTDateDefinition);
	}
	
	public function setTextInputMode($a_value)
	{
		$this->text_input = (bool)$a_value;
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
		
		$date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());		
		$date->setShowTime(false);
		
		if(!(bool)$this->text_input)
		{
			$checked = !(!$adt_date || $adt_date->isNull());
			$date->enableDateActivation($lng->txt("enabled"), $this->addToElementId("tgl"), $checked);
		}
		else
		{
			$date->setMode(ilDateTimeInputGUI::MODE_INPUT);
		}		
		
		$date->setDate($adt_date);
		
		$this->addToParentElement($date);
	}
	
	protected function shouldBeImportedFromPost(array $a_post)
	{
		if(!(bool)$this->text_input)
		{
			return (bool)$a_post["tgl"];			
		}
		return parent::shouldBeImportedFromPost($post);
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
	
		if($post && $this->shouldBeImportedFromPost($post))
		{
			include_once "Services/ADT/classes/class.ilADTDateSearchUtil.php";
			
			if((bool)$this->text_input)
			{
				$date = ilADTDateSearchUtil::handleTextInputPost(ilADTDateSearchUtil::MODE_DATE, $post);
			}
			else
			{
				$date = ilADTDateSearchUtil::handleSelectInputPost(ilADTDateSearchUtil::MODE_DATE, $post);	
			}
			
			// :TODO: all dates are imported as valid 
			
			if($date)
			{
				$date = new ilDate($date, IL_CAL_UNIX);
			}
			
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
		
	
	//  import/export	
		
	public function getSerializedValue()
	{
		if(!$this->isNull() && $this->isValid())		
		{
			return serialize(array($this->getADT()->getDate()->get(IL_CAL_DATE)));			
		}
	}
	
	public function setSerializedValue($a_value)
	{		
		$a_value = unserialize($a_value);
		if(is_array($a_value))
		{
			$this->getADT()->setDate(new ilDate($a_value[0], IL_CAL_DATE));			
		}		
	}
}

?>