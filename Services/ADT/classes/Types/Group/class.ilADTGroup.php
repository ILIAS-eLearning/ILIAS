<?php

class ilADTGroup extends ilADT
{
	protected $elements; // [array]
		
	public function __clone()
	{
		if(is_array($this->elements))
		{
			foreach($this->elements as $id => $element)
			{
				$this->elements[$id] = clone $element;				
			}
		}
	}
	
	
	// definition
	
	protected function isValidDefinition(ilADTDefinition $a_def)
	{
		return ($a_def instanceof ilADTGroupDefinition);
	}
	
	protected function setDefinition(ilADTDefinition $a_def)
	{
		parent::setDefinition($a_def);
		
		$this->elements = array();
		
		foreach($this->getDefinition()->getElements() as $name => $def)
		{
			$this->addElement($name, $def);
		}
	}
	
	
	// defaults
	
	public function reset() 
	{
		parent::reset();
		
		$elements = $this->getElements();
		if(is_array($elements))
		{
			foreach($elements as $element)
			{
				$element->reset();
			}
		}
	}
	
	
	// properties

	protected function addElement($a_name, ilADTDefinition $a_def)
	{
		$this->elements[$a_name] = ilADTFactory::getInstance()->getInstanceByDefinition($a_def);
	}
	
	public function hasElement($a_name)
	{
		return array_key_exists($a_name, $this->elements);
	}
	
	public function getElement($a_name)
	{
		if($this->hasElement($a_name))
		{
			return $this->elements[$a_name];
		}
	}
	
	public function getElements()
	{
		return $this->elements;
	}
	
	
	
	// comparison
	
	public function equals(ilADT $a_adt)
	{
		if($this->getDefinition()->isComparableTo($a_adt))
		{
			return ($this->getCheckSum() == $a_adt->getCheckSum());
		}
	}
				
	public function isLarger(ilADT $a_adt)
	{
		// return null?
	}

	public function isSmaller(ilADT $a_adt)
	{
		// return null?
	}
	
	
	// null
	
	public function isNull()
	{		
		return !sizeof($this->getElements());
	}
	
	
	// validation
	
	public function getValidationErrorsByElements()
	{
		return (array)$this->validation_errors;
	}
	
	public function getValidationErrors()
	{
		return array_keys((array)$this->validation_errors);
	}
	
	protected function addValidationError($a_element_id, $a_error_code)
	{
		$this->validation_errors[(string)$a_error_code] = $a_element_id;
	}
	
	public function isValid()
	{
		$valid = parent::isValid();
		
		if(!$this->isNull())
		{
			foreach($this->getElements() as $element_id => $element)
			{
				if(!$element->isValid())
				{
					foreach($element->getValidationErrors() as $error)
					{
						$this->addValidationError($element_id, $error);			
					}
					$valid = false;
				}
			}			
		}
			
		return $valid;
	}
	
	public function translateErrorCode($a_code)
	{
		if(isset($this->validation_errors[$a_code]))
		{
			$element_id = $this->validation_errors[$a_code];
			$element = $this->getElement($element_id);
			if($element)
			{
				return $element->translateErrorCode($a_code);
			}
		}
	}
	
	
	// check
	
	public function getCheckSum()
	{
		if(!$this->isNull())
		{
			$tmp = array();
			foreach($this->getElements() as $element)
			{
				$tmp[] = $element->getCheckSum();
			}
			return md5(implode(",", $tmp));
		}
	}	
}

?>