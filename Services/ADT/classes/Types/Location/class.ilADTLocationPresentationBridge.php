<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTLocationPresentationBridge extends ilADTPresentationBridge
{
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTLocation);
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
			$map_gui = new ilGoogleMapGUI();
			$map_gui->setMapId("map_".uniqid()); // :TODO: sufficient entropy?
			$map_gui->setLatitude($this->getADT()->getLatitude());
			$map_gui->setLongitude($this->getADT()->getLongitude());
			$map_gui->setZoom($this->getADT()->getZoom());
			$map_gui->setEnableTypeControl(true);
			$map_gui->setEnableLargeMapControl(true);
			$map_gui->setEnableUpdateListener(false);
			$map_gui->setEnableCentralMarker(true);

			return $map_gui->getHtml();			
		}
	}
	
	public function getList()
	{
		if(!$this->getADT()->isNull())
		{
			// :TODO: probably does not make much sense
			return $this->getADT()->getLatitude()."&deg;/".$this->getADT()->getLongitude()."&deg;";
		}
	}
	
	public function getSortable()
	{
		if(!$this->getADT()->isNull())
		{
			// :TODO: probably does not make much sense
			return $this->getADT()->getLatitude().";".$this->getADT()->getLongitude();
		}
	}
}

?>