<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTMultiTextFormBridge extends ilADTFormBridge
{	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTMultiText);
	}
	
	public function addToForm()
	{			
		$text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
		$text->setMulti(true);
		
		$this->addBasicFieldProperties($text, $this->getADT()->getCopyOfDefinition());
		
		$text->setValue($this->getADT()->getTextElements());				
		
		$this->addToParentElement($text);
	}
	
	public function importFromPost()
	{
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setTextElements($this->getForm()->getInput($this->getElementId()));		
		
		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		$field->setValue($this->getADT()->getTextElements());						
	}		
}

?>