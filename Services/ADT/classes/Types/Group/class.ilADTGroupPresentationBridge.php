<?php

require_once "Services/ADT/classes/Bridges/class.ilADTPresentationBridge.php";

class ilADTGroupPresentationBridge extends ilADTPresentationBridge
{
	protected $elements; // [array]
	
	protected function isValidADT(ilADT $a_adt)
	{
		return ($a_adt instanceof ilADTGroup);
	}
	
	protected function prepareElements()
	{		
		if(sizeof($this->elements))
		{
			return;
		}
		
		$this->elements = array();
		$factory = ilADTFactory::getInstance();
		
		// convert ADTs to presentation bridges
		
		foreach($this->getADT()->getElements() as $name => $element)
		{
			$this->elements[$name] = $factory->getPresentationBridgeForInstance($element);
		}			
	}
	
	public function getHTML($delimiter = "<br />")
	{
		$res = array();
		
		$this->prepareElements();
		foreach($this->elements as $element)
		{
			$res[] = $element->getHTML();
		}
		
		if(sizeof($res))
		{
			return implode($delimiter, $res);
		}
	}
	
	public function getSortable($delimiter = ";")
	{
		$res = array();
		
		$this->prepareElements();
		foreach($this->elements as $element)
		{
			$res[] = $element->getSortable();
		}
		
		if(sizeof($res))
		{
			return implode($delimiter, $res);
		}
	}
}

?>