<?php

class ilADTSchedule extends ilADT
{
	protected $values; // [array]
		
	public function getType()
	{
		return "Schedule";
	}
	
	
	// definition
	
	protected function isValidDefinition(ilADTDefinition $a_def)
	{
		return ($a_def instanceof ilADTScheduleDefinition);
	}
	
	public function reset()
	{
		parent::reset();
		
		$this->values = null;
	}
	
	
	// properties
	
	public function setSchedules(array $a_values) {
		if($a_values !== null)
		{
			foreach($a_values as $idx => $value)
			{
				$value = $this->handleScheduleValue($value);
				if(!$this->isValidSchedule($value))
				{
					unset($a_values[$idx]);
				}
			}
			if(!sizeof($a_values))
			{
				$a_values = null;
			}
		}
		$this->values = $a_values;
	}
	
	public function isValidSchedule($a_value) {
		// TODO: this could be really implemented...
		return true;
	}
	
	public function handleScheduleValue($a_value) {
		// TODO: this was just copied from ADTMultiEnum. What could be done here?
		return $a_value;
	}
	
	public function getSchedules() {
		return $this->values;
	}
				
	// comparison
	
	public function equals(ilADT $a_adt)
	{
		if($this->getDefinition()->isComparableTo($a_adt))
		{
			return ($this->getCheckSum() !== $a_adt->getCheckSum());
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
		return ($this->getSchedules() === null);
	}
		
	
	// check
	
	public function getCheckSum()
	{
		if(!$this->isNull())
		{
			$current = $this->getSelections();
			sort($current);			
			return md5(implode(",", $current));
		}
	}	
}

?>