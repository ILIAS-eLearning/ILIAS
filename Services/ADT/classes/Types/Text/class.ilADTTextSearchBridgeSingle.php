<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTTextSearchBridgeSingle extends ilADTSearchBridgeSingle
{
	const SQL_STRICT = 1;
	const SQL_LIKE = 2;
	const SQL_LIKE_END = 3;
	const SQL_LIKE_START = 4;
	
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTTextDefinition);
	}
	
	// table2gui / filter
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{
			$this->getADT()->setText($value);
		}
	}
	
	
	// form
	
	public function addToForm()
	{												
		$text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
		$text->setSize(20);
		$text->setMaxLength(512);
	 	$text->setSubmitFormOnEnter(true);
		
		$text->setValue($this->getADT()->getText());
		
		$this->addToParentElement($text);
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
			
			$this->getADT()->setText($post);			
		}
		else
		{			
			$this->writeFilter();
			$this->getADT()->setText();					
		}	
	}
	
	
	// db
	
	public function getSQLCondition($a_element_id, $a_mode = self::SQL_LIKE)
	{
		global $ilDB;
		
		if(!$this->isNull() && $this->isValid())		
		{
			switch($a_mode)
			{
				case self::SQL_STRICT:
					return $a_element_id." = ".$ilDB->quote($this->getADT()->getText(), "text");	
					
				case self::SQL_LIKE:
					return $ilDB->like($a_element_id, "text", "%".$this->getADT()->getText()."%");
					
				case self::SQL_LIKE_END:
					return $ilDB->like($a_element_id, "text", $this->getADT()->getText()."%");
					
				case self::SQL_LIKE_START:
					return $ilDB->like($a_element_id, "text", "%".$this->getADT()->getText());
			}
						
		}
	}
	
	public function isInCondition(ilADTText $a_adt)
	{
		// :TODO: search mode (see above)
		return $this->getADT()->equals($a_adt);
	}	
}

?>