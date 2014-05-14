<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTIntegerPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTInteger);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			return $this->getADT()->getNumber();
		}
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{
			return $this->getADT()->getNumber();
		}
	}
}

?>