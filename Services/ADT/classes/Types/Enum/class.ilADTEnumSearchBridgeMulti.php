<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeMulti.php";

class ilADTEnumSearchBridgeMulti extends ilADTSearchBridgeMulti
{
	protected $multi_source; // [boo]
	
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{		
		return ($a_adt_def instanceof ilADTEnumDefinition ||
			$a_adt_def instanceof ilADTMultiEnumDefinition);
	}
	
	protected function convertADTDefinitionToMulti(ilADTDefinition $a_adt_def)
	{		
		if($a_adt_def->getType() == "Enum")
		{
			$this->multi_source = false;
			$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("MultiEnum");	
			$def->setNumeric($a_adt_def->isNumeric());
			$def->setOptions($a_adt_def->getOptions());		
			return $def;
		}
		else
		{
			$this->multi_source = true;
			return $a_adt_def;
		}
	}
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{
			$this->getADT()->setSelections($value);
		}
	}
	
	
	// form
	
	public function addToForm()
	{					
		global $lng;		
		
		$def = $this->getADT()->getCopyOfDefinition();
		
		$options = $def->getOptions();
		asort($options); // ?		
		
		$cbox = new ilCheckboxGroupInputGUI($this->getTitle(), $this->getElementId());		
		$cbox->setValue($this->getADT()->getSelections());

		foreach($options as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);		
			$cbox->addOption($option);
		}		
		
		$this->addToParentElement($cbox);	
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
				
		if($post && $this->shouldBeImportedFromPost($post))
		{					
			if($this->getForm() instanceof ilPropertyFormGUI)
			{				
				$item = $this->getForm()->getItemByPostVar($this->getElementId());		
				$item->setValue($post);				
			}
			else if(array_key_exists($this->getElementId(), $this->table_filter_fields))
			{
				$this->table_filter_fields[$this->getElementId()]->setValue($post);				
				$this->writeFilter($post);
			}
			
			if(is_array($post))
			{
				$this->getADT()->setSelections($post);			
			}
		}
		else
		{			
			$this->getADT()->setSelections();			
		}	
	}
	
	
	// db
	
	public function getSQLCondition($a_element_id)
	{
		global $ilDB;
		
		if(!$this->isNull() && $this->isValid())		
		{									
			$type = ($this->getADT() instanceof ilADTMultiEnumText)
				? "text"
				: "integer";
			
			if($this->multi_source)
			{
				include_once "Services/ADT/classes/Types/MultiEnum/class.ilADTMultiEnumDBBridge.php";
				
				$parts = array();
				foreach($this->getADT()->getSelections() as $item)
				{
					$item = "%".ilADTMultiEnumDBBridge::SEPARATOR.
						$item.
						ilADTMultiEnumDBBridge::SEPARATOR."%";							
					$parts[] = $ilDB->like($a_element_id, "text", $item, false);
				}
				return "(".implode(" AND ", $parts).")";				
			}
			
			return $ilDB->in($a_element_id, $this->getADT()->getSelections(), "", $type);				
		}
	}
	
	public function isInCondition(ilADTMultiEnum $a_adt)
	{
		return $this->getADT()->equals($a_adt);
	}	
	
	
	//  import/export	
		
	public function getSerializedValue()
	{		
		if(!$this->isNull() && $this->isValid())		
		{			
			return serialize($this->getADT()->getSelections());
		}		
	}
	
	public function setSerializedValue($a_value)
	{		
		$a_value = unserialize($a_value);
		if(is_array($a_value))
		{
			$this->getADT()->setSelections($a_value);						
		}		
	}
}

?>