<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTLocationSearchBridgeSingle extends ilADTSearchBridgeSingle
{
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTLocationDefinition);
	}
	
	
	// table2gui / filter
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{
			// :TODO:
			// $this->getADT()->setDate(new ilDateTime($value, IL_CAL_DATETIME));
		}
	}
	
	
	// form
	
	public function addToForm()
	{													
		$adt = $this->getADT();
		
		$loc = new ilLocationInputGUI($this->getTitle(), $this->getElementId());
		$loc->setLongitude($adt->getLongitude());
		$loc->setLatitude($adt->getLatitude());
		$loc->setZoom($adt->getZoom());
		
		$loc->setInfo(":TODO: location circum search");
		
		$this->addToParentElement($loc);
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
				
		if($post && $this->shouldBeImportedFromPost($post))
		{								
			$item = $this->getForm()->getItemByPostVar($this->getElementId());
			$item->setLongitude($post["longitude"]);
			$item->setLatitude($post["latitude"]);
			$item->setZoom($post["zoom"]);		
						
			$this->getADT()->setLongitude($post["longitude"]);
			$this->getADT()->setLatitude($post["latitude"]);
			$this->getADT()->setZoom($post["zoom"]);		
		}
		else
		{			
			// :TODO: ?	
		}	
	}
	
	
	// db
	
	public function getSQLCondition($a_element_id)
	{
		global $ilDB;
		
		if(!$this->isNull() && $this->isValid())
		{
			$res = array();			
			$res[] = $a_element_id."_lat = ".$ilDB->quote($this->getADT()->getLatitude(), "float");				
			$res[] = $a_element_id."_long = ".$ilDB->quote($this->getADT()->getLongitude(), "float");				
			return "(".implode(" AND ", $res).")";
		}
	}
	
}

?>