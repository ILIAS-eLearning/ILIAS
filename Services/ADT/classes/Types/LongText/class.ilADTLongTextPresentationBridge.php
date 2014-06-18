<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTLongTextPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTLongText);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			return $this->getADT()->getText();
		}
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{
			return strtolower($this->getADT()->getText());
		}
	}
}

?>