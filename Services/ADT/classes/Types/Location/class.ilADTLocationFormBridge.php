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
		global $lng;
		
		$adt = $this->getADT();
		
		$default = false;
		if($adt->isNull())
		{
			// see ilPersonalProfileGUI::addLocationToForm()
			
			// use installation default
			include_once("./Services/Maps/classes/class.ilMapUtil.php");
			$def = ilMapUtil::getDefaultSettings();
			$adt->setLatitude($def["latitude"]);
			$adt->setLongitude($def["longitude"]);
			$adt->setZoom($def["zoom"]);
			
			$default = true;
		}
		
		// :TODO: title?
		$title = $this->isRequired()
			? $this->getTitle()
			: $lng->txt("location");
			
		$loc = new ilLocationInputGUI($title, $this->getElementId());
		$loc->setLongitude($adt->getLongitude());
		$loc->setLatitude($adt->getLatitude());
		$loc->setZoom($adt->getZoom());
		
		$this->addBasicFieldProperties($loc, $adt->getCopyOfDefinition());
				
		if(!$this->isRequired())
		{
			$optional = new ilCheckboxInputGUI($this->getTitle(), $this->getElementId()."_tgl");
			$optional->addSubItem($loc);
			$this->addToParentElement($optional);
			
			if(!$default && !$adt->isNull())
			{
				$optional->setChecked(true);
			}			
		}		
		else
		{
			$this->addToParentElement($loc);
		}
	}
	
	public function importFromPost()
	{
		$do_import = true;
		if(!$this->isRequired())
		{
			$toggle = $this->getForm()->getInput($this->getElementId()."_tgl");
			if(!$toggle)
			{
				$do_import = false;
			}
		}
		
		if($do_import)
		{
			// ilPropertyFormGUI::checkInput() is pre-requisite
			$incoming = $this->getForm()->getInput($this->getElementId());		
			$this->getADT()->setLongitude($incoming["longitude"]);
			$this->getADT()->setLatitude($incoming["latitude"]);
			$this->getADT()->setZoom($incoming["zoom"]);
		}
		else
		{
			$this->getADT()->setLongitude(null);
			$this->getADT()->setLatitude(null);
			$this->getADT()->setZoom(null);
		}
		
		$field = $this->getForm()->getItemByPostvar($this->getElementId());	
		$field->setLongitude($this->getADT()->getLongitude());
		$field->setLatitude($this->getADT()->getLatitude());
		$field->setZoom($this->getADT()->getZoom());				
	}	
}

?>