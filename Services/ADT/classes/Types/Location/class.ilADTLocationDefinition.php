<?php

class ilADTLocationDefinition extends ilADTDefinition
{	
	// comparison		
		
	public function isComparableTo(ilADT $a_adt)
	{
		// has to be location-based
		return ($a_adt instanceof ilADTLocation);
	}	
}

?>