<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTLongTextFormBridge extends ilADTFormBridge
{
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTLongText);
	}
	
	public function addToForm()
	{		
		$def = $this->getADT()->getCopyOfDefinition();
		$text = new ilTextAreaInputGUI($this->getTitle(), $this->getElementId());
		$text->setUseRte(true);
				
		$this->addBasicFieldProperties($text, $def);
	
		$text->setValue($this->getADT()->getText());	
		
		$this->addToParentElement($text);
	}
	
	public function importFromPost()
	{
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setText($this->getForm()->getInput($this->getElementId()));
	
		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		$field->setValue($this->getADT()->getText());
	}	
}

?>