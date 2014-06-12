<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTFloatPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTFloat);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			$def = $this->getADT()->getCopyOfDefinition();					
			$suffix = $def->getSuffix() ? " ".$def->getSuffix() : null;
			
			// :TODO: language specific?	
			return number_format($this->getADT()->getNumber(), 
				$this->getADT()->getCopyOfDefinition()->getDecimals(), ",", ".").
				$suffix;
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