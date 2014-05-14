<?php

class ilADTInteger extends ilADT
{
	protected $value; // [int]

	
	// definition
	
	protected function isValidDefinition(ilADTDefinition $a_def)
	{
		return ($a_def instanceof ilADTIntegerDefinition);
	}
	
	public function reset()
	{
		parent::reset();
		
		$this->value = null;
	}
	
	
	// properties
			
	public function setNumber($a_value = null)
	{		
		$this->value = $this->getDefinition()->handleNumber($a_value);
	}
	
	public function getNumber()
	{
		return $this->value;
	}

	
	// comparison

	public function equals(ilADT $a_adt)
	{
		if($this->getDefinition()->isComparableTo($a_adt))
		{
			return ($this->getNumber() == $a_adt->getNumber());
		}
	}
				
	public function isLarger(ilADT $a_adt)
	{
		if($this->getDefinition()->isComparableTo($a_adt))
		{
			return ($this->getNumber() > $a_adt->getNumber());
		}
	}
	
	public function isSmaller(ilADT $a_adt)
	{
		if($this->getDefinition()->isComparableTo($a_adt))
		{
			return ($this->getNumber() < $a_adt->getNumber());
		}
	}

	
	// null
	
	public function isNull()
	{
		return ($this->getNumber() === null);
	}
	
	
	// validation
	
	public function isValid()
	{
		$valid = parent::isValid();
		
		$num = $this->getNumber();
		if($num !== null)
		{							
			$min = $this->getDefinition()->getMin();
			if($min !== null && $num < $min)
			{
				$this->addValidationError(self::ADT_VALIDATION_ERROR_MIN);
				$valid = false;
			}

			$max = $this->getDefinition()->getMax();
			if($max !== null && $num > $max)
			{
				$this->addValidationError(self::ADT_VALIDATION_ERROR_MAX);
				$valid = false;
			}
		}
		
		return $valid;
	}
	
	
	// check
	
	public function getCheckSum()
	{
		if(!$this->isNull())
		{
			return (string)$this->getNumber();
		}
	}	
}

?>