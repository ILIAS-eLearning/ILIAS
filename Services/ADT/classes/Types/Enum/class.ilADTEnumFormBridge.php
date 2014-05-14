<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTEnumFormBridge extends ilADTFormBridge
{
	protected $force_radio; // [bool]
	protected $option_infos; // [array]
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTEnum);
	}
	
	public function forceRadio($a_value, array $a_info = null)
	{
		$this->force_radio = (bool)$a_value;
		if($this->force_radio)
		{
			$this->option_infos = $a_info;
		}
	}
	
	public function addToForm()
	{		
		global $lng;
				
		$def = $this->getADT()->getCopyOfDefinition();
		$selection = $this->getADT()->getSelection();
		
		$options = $def->getOptions();
		asort($options); // ?
		
		if(!$this->isRequired())
		{			
			$options = array("" => "-") + $options;			
		}
		else if($this->getADT()->isNull())
		{			
			$options = array("" => $lng->txt("please_select")) + $options;		
		}		

		if(!(bool)$this->force_radio)
		{
			$select = new ilSelectInputGUI($this->getTitle(), $this->getElementId());

			$select->setOptions($options);
		}
		else
		{
			$select = new ilRadioGroupInputGUI($this->getTitle(), $this->getElementId());
			
			foreach($options as $value => $caption)
			{
				$option = new ilRadioOption($caption, $value);
				if(is_array($this->option_infos) && array_key_exists($value, $this->option_infos))
				{
					$option->setInfo($this->option_infos[$value]);
				}
				$select->addOption($option);
			}
		}
		
		$this->addBasicFieldProperties($select, $def);

		$select->setValue($selection);				
		
		$this->addToParentElement($select);
	}
	
	public function importFromPost()
	{
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setSelection($this->getForm()->getInput($this->getElementId()));
	
		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		$field->setValue($this->getADT()->getSelection());
	}	
	
	protected function isActiveForSubItems($a_parent_option = null)
	{		
		return ($this->getADT()->getSelection() == $a_parent_option);		
	}
}

?>