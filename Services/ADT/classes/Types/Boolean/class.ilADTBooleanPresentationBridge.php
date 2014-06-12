<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTBooleanPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTBoolean);
	}
	
	public function getHTML()
	{
		global $lng;
						
		if(!$this->getADT()->isNull())
		{
			// :TODO: force icon?
			
			return $this->getADT()->getStatus() 
				? $lng->txt("yes") 
				: $lng->txt("no");
		}
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{
			// :TODO: cast to int ?
			return $this->getADT()->getStatus() ? 1 : 0;
		}
	}
}

?>