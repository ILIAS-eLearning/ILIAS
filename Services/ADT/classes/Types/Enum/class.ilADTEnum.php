<?php

abstract class ilADTEnum extends ilADT
{
	protected $value; // [string]
	
	
	// definition
	
	protected function isValidDefinition(ilADTDefinition $a_def)
	{
		return ($a_def instanceof ilADTEnumDefinition);
	}
	
	public function reset()
	{
		parent::reset();
		
		$this->value = null;
	}
	
	
	// properties
	
	abstract protected function handleSelectionValue($a_value);
	
	public function setSelection($a_value = null)
	{
		if($a_value !== null)
		{
			$a_value = $this->handleSelectionValue($a_value);
			if(!$this->isValidOption($a_value))
			{
				$a_value = null;
			}
		}
		$this->value = $a_value;
	}
	
	public function getSelection()
	{
		return $this->value;
	}
	
	public function isValidOption($a_value)
	{		
		$a_value = $this->handleSelectionValue($a_value);
		return array_key_exists($a_value, $this->getDefinition()->getOptions());
	}
	
	
	// comparison
	
	public function equals(ilADT $a_adt)
	{
		if($this->getDefinition()->isComparableTo($a_adt))
		{
			return ($this->getSelection() === $a_adt->getSelection());
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
		return ($this->getSelection() === null);
	}
		
	
	// check
	
	public function getCheckSum()
	{
		if(!$this->isNull())
		{
			return (string)$this->getSelection();
		}
	}	
}

?>