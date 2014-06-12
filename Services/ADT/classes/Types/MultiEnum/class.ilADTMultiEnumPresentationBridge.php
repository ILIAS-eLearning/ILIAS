<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTMultiEnumPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTMultiEnum);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			$res = array();
			
			$options = $this->getADT()->getCopyOfDefinition()->getOptions();
			foreach($this->getADT()->getSelections() as $value)
			{
				$res[] = $options[$value];
			}
						
			return implode(", ", $res);
		}		
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{			
			return implode(";", $this->getADT()->getSelections());
		}		
	}
}

?>