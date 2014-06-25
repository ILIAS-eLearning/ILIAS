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
	
	public function getSQLCondition($a_element_id, $a_mode = self::SQL_LIKE, $a_value = null)
	{
		global $ilDB;
		
		if(!$a_value)
		{
			if($this->isNull() || !$this->isValid())		
			{
				return;
			}
			$a_value = $this->getADT()->getText();				
		}
						
		switch($a_mode)
		{
			case self::SQL_STRICT:
				if(!is_array($a_value))
				{
					return $a_element_id." = ".$ilDB->quote($a_value, "text");	
				}
				else
				{
					return $ilDB->in($a_element_id, $a_value, "", "text");
				}
				break;

			case self::SQL_LIKE:
				if(!is_array($a_value))
				{
					return $ilDB->like($a_element_id, "text", "%".$a_value."%");
				}
				else
				{
					$tmp = array();
					foreach($a_value as $word)
					{
						if($word)
						{
							$tmp[] = $ilDB->like($a_element_id, "text", "%".$word."%");
						}
					}
					if(sizeof($tmp))
					{
						return "(".implode(" OR ", $tmp).")";
					}
				}
				break;

			case self::SQL_LIKE_END:
				if(!is_array($a_value))
				{
					return $ilDB->like($a_element_id, "text", $a_value."%");
				}
				break;

			case self::SQL_LIKE_START:
				if(!is_array($a_value))
				{
					return $ilDB->like($a_element_id, "text", "%".$a_value);
				}
				break;
		}						
	}
	
	public function isInCondition(ilADTText $a_adt)
	{
		// :TODO: search mode (see above)
		return $this->getADT()->equals($a_adt);
	}	
	
	
	//  import/export	
		
	public function getSerializedValue()
	{		
		if(!$this->isNull() && $this->isValid())		
		{			
			return serialize(array($this->getADT()->getText()));
		}		
	}
	
	public function setSerializedValue($a_value)
	{		
		$a_value = unserialize($a_value);
		if(is_array($a_value))
		{
			$this->getADT()->setText($a_value[0]);						
		}		
	}
}

?>