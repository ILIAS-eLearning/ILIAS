<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTMultiTextPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTMultiText);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			return implode(", ", $this->getADT()->getTextElements());
		}
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{
			return implode(";", $this->getADT()->getTextElements());
		}
	}
}

?>