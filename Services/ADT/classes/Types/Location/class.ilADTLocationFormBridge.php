<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTLocationFormBridge extends ilADTFormBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTLocation);
	}
	
	public function addToForm()
	{		
		$adt = $this->getADT();
		
		$loc = new ilLocationInputGUI($this->getTitle(), $this->getElementId());
		$loc->setLongitude($adt->getLongitude());
		$loc->setLatitude($adt->getLatitude());
		$loc->setZoom($adt->getZoom());
		
		$this->addBasicFieldProperties($loc, $adt->getCopyOfDefinition());
		
		$this->addToParentElement($loc);
	}
	
	public function importFromPost()
	{
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$incoming = $this->getForm()->getInput($this->getElementId());		
		$this->getADT()->setLongitude($incoming["longitude"]);
		$this->getADT()->setLatitude($incoming["latitude"]);
		$this->getADT()->setZoom($incoming["zoom"]);
		
		$field = $this->getForm()->getItemByPostvar($this->getElementId());	
		$field->setLongitude($this->getADT()->getLongitude());
		$field->setLatitude($this->getADT()->getLatitude());
		$field->setZoom($this->getADT()->getZoom());				
	}	
}

?>