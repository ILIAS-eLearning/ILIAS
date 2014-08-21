<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTDateTimeFormBridge extends ilADTFormBridge
{
	protected $invalid_input; // [bool]
	protected $text_input; // [bool]
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTDateTime);
	}
	
	public function setTextInputMode($a_value)
	{
		$this->text_input = (bool)$a_value;
	}
	
	public function addToForm()
	{			
		global $lng;
			
		$adt_date = $this->getADT()->getDate();

		$date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());
		$date->setShowTime(true);

		$this->addBasicFieldProperties($date, $this->getADT()->getCopyOfDefinition());		
		
		if((bool)$this->text_input)
		{
			$date->setMode(ilDateTimeInputGUI::MODE_INPUT);			
		}	
		else
		{
			if(!$this->isRequired())
			{			
				$date->enableDateActivation("", $this->getElementId()."_tgl", !(!$adt_date || $adt_date->isNull()));
			}
		}

		$date->setDate($adt_date);				

		$this->addToParentElement($date);		
	}
	
	public function importFromPost()
	{
		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		
		// :TODO: refactor ilDateTimeInputGUI
				
		// because of ilDateTime the ADT can only have valid dates
		
		if(!$field->invalid_input)
		{	
			$date = null;

			$toggle = true;
			
			if(!$this->isRequired() &&
				!(bool)$this->text_input)
			{
				// :TODO: should be handle by ilDateTimeInputGUI
				$toggle = $_POST[$field->getActivationPostVar()];
			}

			if($toggle)
			{
				// ilPropertyFormGUI::checkInput() is pre-requisite
				$incoming = $this->getForm()->getInput($this->getElementId());
				if($incoming["date"] && $incoming["time"])
				{
					$date = new ilDateTime($incoming["date"]." ".$incoming["time"], IL_CAL_DATETIME);
				}
			}
				
			$this->getADT()->setDate($date);

			$field->setDate($this->getADT()->getDate());
		}
		else
		{
			$this->invalid_input = true;
		}		
	}	
	
	public function validate()
	{		
		// :TODO: error handling is done by ilDateTimeInputGUI
		return !(bool)$this->invalid_input;
	}
}

?>