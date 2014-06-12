<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTDatePresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTDate);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			// :TODO: relative dates?
			
			return ilDatePresentation::formatDate($this->getADT()->getDate());
		}
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{
			return (int)$this->getADT()->getDate()->get(IL_CAL_UNIX);
		}
	}
}

?>