<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTFloatSearchBridgeSingle extends ilADTSearchBridgeSingle
{
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTFloatDefinition);
	}
	
	
	// form
	
	public function addToForm()
	{									
		$def = $this->getADT()->getCopyOfDefinition();
		
		$number = new ilNumberInputGUI($this->getTitle(), $this->getElementId());		
		$number->setSize(10);
		$number->setDecimals($def->getDecimals());
		
		$min = $def->getMin();
		if($min !== null)
		{
			$number->setMinValue($min);
		}
		
		$max = $def->getMax();
		if($max !== null)
		{
			$number->setMaxValue($max);
			
			$length = strlen($max);
			$number->setSize($length);
			$number->setMaxLength($length);
		}
		
		$this->addToParentElement($number);
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
				
		if($post && $this->shouldBeImportedFromPost($post))
		{					
			$item = $this->getForm()->getItemByPostVar($this->getElementId());		
			$item->setValue($post);	
			
			$this->getADT()->setNumber($post);			
		}
		else
		{			
			$this->getADT()->setNumber();			
		}	
	}
	
	
	// db
	
	public function getSQLCondition($a_element_id)
	{
		global $ilDB;
		
		if(!$this->isNull() && $this->isValid())
		{
			return $a_element_id." = ".$ilDB->quote($this->getADT()->getNumber(), "float");				
		}
	}
	
	public function isInCondition(ilADTFloat $a_adt)
	{
		return $this->getADT()->equals($a_adt);
	}		
}

?>