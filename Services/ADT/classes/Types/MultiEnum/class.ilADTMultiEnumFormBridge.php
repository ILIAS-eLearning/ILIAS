<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTMultiEnumFormBridge extends ilADTFormBridge
{
	protected $option_infos; // [array]
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTMultiEnum);
	}
	
	public function setOptionInfos(array $a_info = null)
	{
		$this->option_infos = $a_info;
	}
	
	public function addToForm()
	{		
		global $lng;
				
		$def = $this->getADT()->getCopyOfDefinition();
		
		$options = $def->getOptions();
		// asort($options); // ?

		$cbox = new ilCheckboxGroupInputGUI($this->getTitle(), $this->getElementId());

		foreach($options as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			if(is_array($this->option_infos) && array_key_exists($value, $this->option_infos))
			{
				$option->setInfo($this->option_infos[$value]);
			}
			$cbox->addOption($option);
		}		
		
		$this->addBasicFieldProperties($cbox, $def);

		$cbox->setValue($this->getADT()->getSelections());				
		
		$this->addToParentElement($cbox);
	}
	
	public function importFromPost()
	{
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setSelections($this->getForm()->getInput($this->getElementId()));
	
		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		$field->setValue($this->getADT()->getSelections());
	}	
	
	protected function isActiveForSubItems($a_parent_option = null)
	{		
		$current = $this->getADT()->getSelections();
		return (is_array($current) && in_array($a_parent_option, $current));		
	}
}

?>