<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTLocationPresentationBridge extends ilADTPresentationBridge
{
	protected $width; // [mixed]
	protected $height; // [mixed]
	
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTLocation);
	}
	
	public function setSize($a_width, $a_height)
	{
		$this->width = $a_width;
		$this->height = $a_height;
	}
	
	public function getHTML()
	{
		if(!$this->getADT()->isNull())
		{
			include_once("./Services/Maps/classes/class.ilMapUtil.php");
			$map_gui = ilMapUtil::getMapGUI();
			$map_gui->setMapId("map_".uniqid()) // :TODO: sufficient entropy?
					->setLatitude($this->getADT()->getLatitude())
					->setLongitude($this->getADT()->getLongitude())
					->setZoom($this->getADT()->getZoom())
					->setEnableTypeControl(true)
					->setEnableLargeMapControl(true)
					->setEnableUpdateListener(false)
					->setEnableCentralMarker(true);
			
			if($this->width)
			{
				$map_gui->setWidth($this->width);
			}
			if($this->height)
			{
				$map_gui->setHeight($this->height);
			}
			
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